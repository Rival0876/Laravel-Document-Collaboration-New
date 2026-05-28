import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Collaboration from '@tiptap/extension-collaboration';
import CollaborationCursor from '@tiptap/extension-collaboration-cursor';
import * as Y from 'yjs';
import { WebrtcProvider } from 'y-webrtc';

if (document.getElementById('editor-container')) {
    try {
        const config = window.APP_CONFIG;

        // 1. SETUP YJS & WEBRTC (Untuk sinkronisasi teks super cepat)
        const ydoc = new Y.Doc();
        const provider = new WebrtcProvider(`valdidocs-room-${config.docId}`, ydoc);

        // 2. FITUR REVERB (Presence - Hanya jalan jika Echo aktif)
        if (window.Echo) {
            const channel = window.Echo.join(`document.${config.docId}`);
            
            const updateUsersUI = (users) => {
                const activeContainer = document.getElementById('active-users');
                if (activeContainer) {
                    activeContainer.innerHTML = users.map(u => 
                        `<span id="user-badge-${u.id}" style="background-color: ${u.color}" class="text-white px-2 py-1 rounded text-xs font-bold shadow-sm">${u.name}</span>`
                    ).join('');
                }
            };

            let activeUsers = [];
            channel.here((users) => { 
                activeUsers = users; 
                updateUsersUI(activeUsers); 
            })
            .joining((user) => { 
                // CEGAH DUPLIKAT: Cek dulu apakah user sudah ada di dalam list
                if (!activeUsers.some(u => u.id === user.id)) {
                    activeUsers.push(user); 
                }
                updateUsersUI(activeUsers); 
            })
            .leaving((user) => { 
                // PERBAIKAN: Timpa variabel activeUsers dengan array yang baru (tanpa user yang keluar)
                activeUsers = activeUsers.filter(u => u.id !== user.id); 
                updateUsersUI(activeUsers); 
            });
        }

        // Menyiapkan variabel untuk menahan timer autosave
        let autoSaveTimeout;
        const statusIndicator = document.getElementById('save-status');

        // 3. INISIALISASI TIPTAP EDITOR
        const editor = new Editor({
            element: document.getElementById('editor-container'),
            extensions: [
                StarterKit.configure({ history: false }),
                Underline,
                Collaboration.configure({ document: ydoc }),
                CollaborationCursor.configure({
                    provider: provider,
                    user: { name: config.user.name, color: config.user.color },
                }),
            ],
            onCreate({ editor }) {
                const initialHTML = document.getElementById('initial-content').innerHTML;
                
                // Beri jeda 800ms agar WebRTC bisa sinkronisasi antar-peer terlebih dahulu
                setTimeout(() => {
                    // Jika setelah jeda editor MASIH kosong (berarti tidak ada teman yang online), 
                    // baru kita muat dari database
                    if (editor.isEmpty && initialHTML.trim() !== '') {
                        editor.commands.setContent(initialHTML);
                    }
                }, 800);
            },
            onUpdate({ editor }) {
                if (statusIndicator) statusIndicator.innerText = 'Mengetik...';

                // Hapus timer sebelumnya agar tidak spam server
                clearTimeout(autoSaveTimeout);

                // Setel timer baru: tunggu 2 detik setelah user diam, baru jalankan save
                autoSaveTimeout = setTimeout(() => {
                    if (statusIndicator) statusIndicator.innerText = 'Menyimpan...';

                    window.axios.post(`/documents/${config.docId}/autosave`, {
                        content: editor.getHTML()
                    }).then(() => {
                        if (statusIndicator) statusIndicator.innerText = 'Tersimpan (Auto)';
                    }).catch(() => {
                        if (statusIndicator) statusIndicator.innerText = 'Gagal menyimpan!';
                    });
                }, 2000);
            }
        });

        // 4. ACTION BUTTONS TOOLBAR
        document.getElementById('btn-bold')?.addEventListener('click', () => editor.chain().focus().toggleBold().run());
        document.getElementById('btn-italic')?.addEventListener('click', () => editor.chain().focus().toggleItalic().run());
        document.getElementById('btn-underline')?.addEventListener('click', () => editor.chain().focus().toggleUnderline().run());

        // 5. FITUR SAVE VERSION (DIPERBAIKI: TANPA RELOAD HALAMAN)
        const btnSave = document.getElementById('btn-save');
        if (btnSave) {
            btnSave.addEventListener('click', function() {
                const summary = prompt("Ketik ringkasan perubahan (misal: 'Menyelesaikan Bab 1'):");
                
                if (summary) {
                    const currentContent = editor.getHTML();
                    
                    // Ubah text tombol jadi loading
                    btnSave.innerText = '⏳ Menyimpan...';
                    btnSave.disabled = true;

                    window.axios.post(`/documents/${config.docId}/version`, {
                        content: currentContent,
                        summary: summary
                    }).then(res => {
                        // Kembalikan tombol seperti semula
                        btnSave.innerText = '💾 Simpan Versi';
                        btnSave.disabled = false;
                        alert(res.data.message || 'Versi dokumen berhasil direkam!');

                        // Hapus teks tulisan "Belum ada riwayat" jika ada di kanan
                        document.getElementById('no-version-text')?.remove();

                        // Masukkan komponen riwayat baru ke pembungkus sebelah kanan secara instan
                        const container = document.getElementById('version-container');
                        if (container) {
                            // Amankan kutip dua agar struktur HTML data-content tidak rusak
                            const escapedContent = currentContent.replace(/"/g, '&quot;');
                            
                            const newVersionHtml = `
                                <div class="p-3 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-indigo-50 cursor-pointer transition version-item" data-content="${escapedContent}">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-bold text-indigo-600">${config.user.name}</span>
                                        <span class="text-xs text-gray-500">Baru saja</span>
                                    </div>
                                    <p class="text-sm text-gray-700 italic">${summary}</p>
                                </div>
                            `;
                            // Sisipkan komponen baru ini di posisi paling atas list
                            container.insertAdjacentHTML('afterbegin', newVersionHtml);
                        }
                    }).catch(err => {
                        btnSave.innerText = '💾 Simpan Versi';
                        btnSave.disabled = false;
                        alert('Gagal menyimpan versi baru.');
                        console.error(err);
                    });
                }
            });
        }

        // 6. FITUR KLIK RIWAYAT VERSI (DIPERBAIKI: MENGGUNAKAN EVENT DELEGATION)
        document.addEventListener('click', function(e) {
            // Cari elemen terdekat yang memiliki class .version-item
            const versionItem = e.target.closest('.version-item');
            
            if (versionItem) {
                const htmlContent = versionItem.getAttribute('data-content');
                
                if (confirm('Apakah kamu yakin ingin memuat riwayat versi ini? Teks yang belum disimpan di editor saat ini akan hilang.')) {
                    // Isi teks ke dalam Editor Tiptap
                    editor.commands.setContent(htmlContent);
                    
                    // Langsung jalankan perintah auto-save ke database agar tersinkron permanen
                    window.axios.post(`/documents/${config.docId}/autosave`, {
                        content: htmlContent
                    }).then(() => {
                        if (statusIndicator) statusIndicator.innerText = 'Versi dimuat & tersimpan!';
                    });
                }
            }
        });

        // Jadikan fungsi global agar bisa dipanggil dari onclick HTML
window.loadVersionHistory = function(versionId) {
    const hiddenContentDiv = document.getElementById(`history-content-${versionId}`);
    
    if (hiddenContentDiv) {
        // Ambil kode HTML dari dalam div
        const htmlContent = hiddenContentDiv.innerHTML;
        
        // Timpa isi Tiptap Editor dengan konten dari masa lalu tersebut
        // (Pastikan variabel 'editor' kamu bisa diakses secara global di sini)
        window.editor.commands.setContent(htmlContent);
    } else {
        console.error("Gagal memuat konten versi ini.");
    }
};

    } catch (error) {
        console.error("Ada kendala pada Editor: ", error);
    }


}