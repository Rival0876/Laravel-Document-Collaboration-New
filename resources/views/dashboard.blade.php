<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Valdidocs Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm mb-6">
                <form action="{{ route('documents.store') }}" method="POST" class="flex gap-4">
                    @csrf
                    <input type="text" name="title" placeholder="Masukkan judul dokumen baru..." class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">
                        + Buat Dokumen
                    </button>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-bold text-lg mb-4">Dokumen Kamu:</h3>
                @if($documents->isEmpty())
                    <p class="text-gray-500">Belum ada dokumen. Silakan buat dokumen baru di atas!</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($documents as $doc)
                            <a href="{{ route('documents.show', $doc->id) }}" class="block p-4 border rounded-lg hover:border-blue-500 transition shadow-sm bg-gray-50">
                                <h4 class="font-semibold text-blue-600 truncate">{{ $doc->title }}</h4>
                                <p class="text-xs text-gray-400 mt-2">Dibuat: {{ $doc->created_at->diffForHumans() }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
