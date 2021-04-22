<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AjaxCrudController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Si, viene un request a mi ajax entonces genera el siguiente codigo
        if(request()->ajax())
        {
            //retorna un datatables, que obtenga del modelo el ultimo agregado
            return datatables()->of(AjaxCrud::latest()->get())
                    //Mando a llamar el metodo agregar columna del modelo, mandandole
                    //la accion realizada y los datos que vienen en la peticion HTML
                   ->addColumn('action',function($data){
                       //Creamos los botones en columnas para editar y eliminar
                       $button = '<button type="button" name="edit" id="'.$data->id.'" class="edit btn btn-primary btn-sm">Editar</button>';
                       $button .= '&nbsp;&nbsp;';
                       $button .= '<button type="button" name="delete" id="'.$data->id.'" class="delete btn btn-primary btn-sm">Eliminar</button>';
                   })
                   //Maneja las columnas
                   -rawColumns(['action'])
                   //Lo hago visible
                   ->make(true);
        }
        //En el caso de que el request no sea posible, entonces regresa al index
        return view('ajax_index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Reglas predefinidas para validar informacion
        $reglas = array(
            'nombres' => 'required',
            'apellidos' => 'required',
            'imagen' => 'required|image|max:2048'
        );

        //Llamamos al validador de Laravel que compare lo del request con las reglas
        $error = Validator::make($request-all(),$reglas);

        //Este if revisa si hubieron errores y los devuelve a la vista
        if($error->fails())
        {
            return response()->json(['errors' => $error()->all()]);
        }

        //Maneja la imagen subida, le genera un nuevo nombre, su extension y lo almacena en la app
        $image = $request->file('imagen');
        $nuevo_nombre = rand().'.'.$image->getClientOriginalExtension();
        $image-> move(public_path('imagenes'),$nuevo_nombre);

        //Creamos un arreglo con los datos del formulario
        $datos_form = array(
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'imagen' => $nuevo_nombre
        );

        //Insertamos los valores en la base de datos
        AjaxCrud::create($datos_form);

        //Devolvemos a la vista que los datos se agregaron correctamente
        return reponser()->json(['success' => 'Datos agregados correctamente']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //   
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //Si viene una peticion desde ajax para actualizar, obtiene la info y la devuelve a los campos
        if(request()->ajax())
        {
            $data = AjaxCrud::findOrFail($id);
            return response()->json(['data' => $data]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Primero trabajamos con las imagenes
        $nombre_imagen = $request->hidden_image;
        $image = $request->file('imagen');
        if($image != '')
        {
            //Reglas predefinidas para validar informacion
            $reglas = array(
                'nombres' => 'required',
                'apellidos' => 'required',
                'imagen' => 'image|max:2048'
            );

            //Llamamos al validador de Laravel que compare lo del request con las reglas
            $error = Validator::make($request-all(),$reglas);

            //Este if revisa si hubieron errores y los devuelve a la vista
            if($error->fails())
            {
                return response()->json(['errors' => $error()->all()]);
            }

            //Creamos un nuevo nombre para el archivo
            $nombre_imagen = rand().'.'.$image->getClientOriginalExtension();
            $image-> move(public_path('imagenes'),$nombre_imagen);

        }
        else
        {
            //Reglas predefinidas para validar informacion
            $reglas = array(
                'nombres' => 'required',
                'apellidos' => 'required'
            );

            //Llamamos al validador de Laravel que compare lo del request con las reglas
            $error = Validator::make($request-all(),$reglas);

            //Este if revisa si hubieron errores y los devuelve a la vista
            if($error->fails())
            {
                return response()->json(['errors' => $error()->all()]);
            }
        }

        //Formamos el array de datos
        $datos_form = array(
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'imagen' => $nombre_imagen
        );

        //Actualizamos en la db
        AjaxCrud::whereId($request->hidden_id)->update($datos_form);

        return response()->json(['success' => 'Los datos se actualizaron correctamente']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Busca con el id si existe el valor, lo elimina
        $data = AjaxCrud::findOrFail($id);
        $data->delete();
    }
}
