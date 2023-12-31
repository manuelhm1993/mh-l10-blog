<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads; // Trait para cargar imágenes
use Livewire\WithPagination; //  Trait para hacer paginaciones reactivas

use App\MH\Classes\Helper;
use App\Models\Post;

use Illuminate\Support\Facades\Storage;

class ShowPosts extends Component
{
    use WithFileUploads;
    use WithPagination;

    // --------------- Propiedades del componente
    public $search = '';
    public $sort = 'id';
    public $direction = 'desc';

    // Editar un post
    public $post, $open_edit = false, $image, $identificador;

    // Selector de items a mostrar
    public $entradas = [10, 25, 50, 100];
    public $itemsPagina = '10'; // Volverlo un string para poder ocultarlo en el queryString

    // Aplazar la carga, uso de spinners
    public $readyToLoad = false;

    // Permite vinvular las propiedades directamente en un input
    protected $rules = [
        'post.title'   => 'required',
        'post.content' => 'required',
    ];

    // --------------- Oyentes de eventos
    //
    // Nombre del evento y método que lo escucha, el evento render, ejecuta el método render
    protected $listeners = [
        'render',
        'delete',
    ];

    // Guardar el estado actual de la página (guarda en url el valor de itemsPagina)
    protected $queryString = [
        'itemsPagina' => ['except' => '10'], // Ocultar el valor por defecto de la url
        'sort'        => ['except' => 'id'],
        'direction'   => ['except' => 'desc'],
        'search'      => ['except' => ''],
    ];

    // --------------- Este método renderiza el contenido dentro del componente show-posts
    public function render()
    {
        if($this->readyToLoad) {
            // --------------- Campo de búsqueda por título y contenido
            $posts = Post::where('title', 'like', "%{$this->search}%")
                         ->orWhere('content', 'like', "%{$this->search}%")
                         ->orderBy($this->sort, $this->direction)
                         ->paginate(intval($this->itemsPagina)); // Convertir itemsPagina en entero
        }
        else {
            $posts = [];
        }

        return view('livewire.show-posts', compact('posts')); // Toma el layout principal layouts.app
        // --------------- Se puede especificar el layout del que se extiende
        // ->layout('layouts.base');
    }

    // --------------- Este método se encarga de ordenar los registros
    public function order($sort) {
        // --------------- Alterna entre ascendente y descendente
        if($this->sort === $sort) {
            $this->direction = ($this->direction === 'desc') ? 'asc' : 'desc';
        }
        else {
            $this->sort = $sort;
            $this->direction = 'asc';
        }
    }

    public function edit(Post $post) {
        $this->post = $post;
        $this->open_edit = true;
        $this->identificador = Helper::generateID();
    }

    public function update() {
        // El método validate permite llenar el post con los nuevos datos si pasan la validación
        $this->validate();

        // Validar si se seleccionó una nueva imagen
        if($this->image) {
            // Eliminar la imagen actual del post
            Storage::disk('public')->delete($this->post->image);

            // Guardar la nueva imagen en el directorio posts posts y modificar la url del post
            $this->post->image = $this->image->store('posts', 'public');
        }

        $this->post->save();
        $this->resetFields();
        $this->emit('feedbackSA2', '¡Post actualizado!', 'La acción fue ejecutada exitosamente.');
    }

    // ----------- Resetear las propiedades del componente
    public function resetFields() {
        $this->reset(['open_edit', 'image']);
        $this->identificador = Helper::generateID();
    }

    // Resetear filtrados de búsqueda con el trait de paginación: updatingNombrePropiedad
    public function updatingSearch() {
        $this->resetPage();
    }

    public function updatingItemsPagina() {
        $this->resetPage();
    }

    // Renderiza la sección de posts cuando carga todo el documento
    public function loadPosts() {
        $this->readyToLoad = true;
    }

    // Método para borrar post usando implicit binding
    public function delete(Post $post) {
        $post->delete();
    }
}
