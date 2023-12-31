<div>
    {{-- Poner a la escucha del click y usar un método mágico para establecer la propiedad open --}}
    <x-danger-button wire:click="$set('open', true)">
        Crear nuevo post
    </x-danger-button>

    {{-- Vincultar la propiedad open --}}
    <x-dialog-modal wire:model="open">
        <x-slot:title>
            Crear nuevo post
        </x-slot:title>

        <x-slot:content>
            {{-- Alert que se muestra como feedback al estar procesando una imagen --}}
            <x-mh.load-img-alert :image="$image" />

            <div class="mb-4">
                {{-- Hacer uso de los componentes de jetstream para facilitar la maquetación --}}
                <x-label value="Título del post" />

                {{-- Sincronizar el formulario con las propiedades el componente --}}
                <x-input type="text" class="w-full" wire:model.defer="title" />

                {{-- Llamar al componente de validación de input jetstream --}}
                <x-input-error for="title" />
            </div>

            {{-- wire:ignore hace que no se renderice este elemento al ocurrir un cambio --}}
            <div class="mb-4" >
                <x-label value="Contenido del post" />
                {{-- Para evitar el renderizado de la vista por cada letra que se escribe en los inputs se usa wire:model.defer --}}

                {{-- Utilizar las clases de tailwind para crear un form-control personal y aplicarlo a un texarea común --}}
                <div wire:ignore>
                    <textarea
                        id="editor"
                        rows="6"
                        class="form-control w-full"
                        wire:model.defer="content"
                    >
                    </textarea>
                </div>
                {{-- Llamar al componente de validación de input jetstream --}}
                <x-input-error for="content" />
            </div>

            {{-- Campo para guardar imágenes --}}
            <div class="mb-4">
                <input type="file" wire:model.defer="image" id="{{$identificador}}">
                <x-input-error for="image" />
            </div>
        </x-slot:content>

        <x-slot:footer>
            {{-- Botón de acción para cerrar el modal --}}
            <x-secondary-button class="mr-3" wire:click="resetFields">
                Cancelar
            </x-secondary-button>

            {{-- Escucha el click y llama al método store y en el proceso desactiva el botón y lo pone 25% más opaco --}}
            <x-danger-button wire:click="store" wire:loading.attr="disabled" wire:target="store, image" class="disabled:opacity-25">
                Crear post
            </x-danger-button>
        </x-slot:footer>
    </x-dialog-modal>

    {{-- Llamar al stack js del layout app --}}
    @push('js')
        {{-- CDN de CKeditor5 --}}
        <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

        {{-- Crear una instancia de CKeditor --}}
        <script>
            ClassicEditor
                .create( document.querySelector( '#editor' ) )
                .then((editor) => {
                    editor.model.document.on('change:data', () => {
                        @this.set('content', editor.getData());
                    });

                    // Resetear el CKEditor
                    Livewire.on('resetCKEditor', () => {
                        editor.setData('');
                    });
                })
                .catch( error => {
                    console.error( error );
                } );
        </script>
    @endpush
</div>
