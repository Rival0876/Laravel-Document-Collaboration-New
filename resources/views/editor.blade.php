<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Menedit: {{ $document->title }}
            </h2>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Sedang aktif:</span>
                <div id="active-users" class="flex gap-1"></div>
            </div>
        </div>
    </x-slot>

    <style>
        .ProseMirror { min-height: 400px; outline: none; }
        .collaboration-cursor__caret {
            border-left: 2px solid #0D0D0D;
            border-right: 2px solid #0D0D0D;
            margin-left: -1px; margin-right: -1px;
            pointer-events: none; position: relative; word-break: normal;
        }
        .collaboration-cursor__label {
            border-radius: 3px 3px 3px 0; color: #fff;
            font-size: 12px; font-weight: 600;
            left: -1px; padding: 0.1rem 0.3rem;
            position: absolute; top: -1.4em;
            user-select: none; white-space: nowrap;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
            
            <div class="w-full md:w-3/4 bg-white p-6 rounded-lg shadow-sm">
                <div class="mb-4 flex items-center gap-2 border-b pb-4">
                    <button id="btn-bold" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 font-bold">B</button>
                    <button id="btn-italic" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 italic">I</button>
                    <button id="btn-underline" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 underline">U</button>
                    
                    <div class="ml-auto flex items-center">
                        <span id="save-status" class="text-sm text-gray-400 mr-3 italic"></span>
                        <button id="btn-save" class="px-4 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold text-sm shadow-sm">
                            💾 Simpan Versi
                        </button>
                    </div>
                </div>
                
                <div id="editor-container" class="border p-4 rounded-md"></div>
                
                <div id="initial-content" style="display: none;">{!! $document->content !!}</div>
                
                <script>
                    window.APP_CONFIG = {
                        docId: parseInt("{{ $document->id }}"),
                        user: {
                            id: parseInt("{{ auth()->id() }}"),
                            name: "{{ auth()->user()->name }}",
                            color: '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0')
                        }
                    };
                </script>
            </div>

            <div class="w-full md:w-1/4 bg-gray-50 p-4 rounded-lg shadow-sm border h-fit">
                <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Riwayat Versi</h3>
                
                <div id="version-container" class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                    @if($versions->isEmpty())
                        <p id="no-version-text" class="text-sm text-gray-500">Belum ada riwayat penyimpanan.</p>
                    @else
                        @foreach($versions as $version)
                            <div class="p-3 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-indigo-50 cursor-pointer transition version-item" 
                                 onclick="loadVersionHistory({{ $version->id }})">
                                 
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-bold text-indigo-600">{{ $version->user->name ?? 'User' }}</span>
                                    <span class="text-xs text-gray-500">{{ $version->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-gray-700 italic">{{ $version->summary ?? 'Menyimpan perubahan' }}</p>

                                <div id="history-content-{{ $version->id }}" class="hidden">{!! $version->content !!}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>