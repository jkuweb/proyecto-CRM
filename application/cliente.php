<?php
require_once 'domicilio.php';
require_once 'datodecontacto.php';
require_once 'pedido.php';
require_once 'usuario.php';
require_once 'core/template.php';
require_once 'common/view.php';


class Cliente {

    function __construct() {    
        $this->cliente_id = 0;
        $this->denominacion = "";
        $this->nif = "";
        $this->domicilio = new Domicilio(); 
        $this->datodecontacto_collection = [];
        $this->pedido_collection = [];
    }

    function insert() {
        $sql = "INSERT INTO     cliente
                                (denominacion, nif, domicilio) 
				VALUES          (?, ?, ?)";

        $datos = [
            $this->denominacion,
            $this->nif,
            $this->domicilio->domicilio_id
        ];
        $this->cliente_id = consultar($sql, $datos);
    }
    
    function select() {
        $sql = "SELECT  denominacion, nif, domicilio 
                FROM    cliente 
                WHERE   cliente_id = ?";
        $datos = [$this->cliente_id];
        $resultado = consultar($sql, $datos)[0];

        $this->denominacion = $resultado['denominacion'];
        $this->nif = $resultado['nif'];
        $this->domicilio->domicilio_id = $resultado['domicilio'];
        $this->domicilio->select();

        $datodecontactos = DatoDeContacto::get_datodecontacto($this->cliente_id);
        foreach($datodecontactos as $array) {
            $datodecontacto = new DatoDeContacto();
            $datodecontacto->datodecontacto_id = $array['datodecontacto_id'];
            $datodecontacto->select();
            $this->datodecontacto_collection[] = $datodecontacto;
        } 

        $pedidos = PedidoDataHelper::get_pedido($this->cliente_id);
        if(!empty($pedidos)) {  # FIXME 
            foreach($pedidos as $array) {
                $pedido = new Pedido();
                $pedido->pedido_id = $array['pedido_id'];
                $pedido->select();
                $this->pedido_collection[] = $pedido;
            }
        }
    }

    function update() {
        $sql = "UPDATE  cliente 
                SET     denominacion = ?, nif = ?, domicilio = ? 
                WHERE   cliente_id = ?";
        $datos = [
            $this->denominacion,
            $this->nif,
            $this->domicilio->domicilio_id,
            $this->cliente_id
        ];
        consultar($sql, $datos);
    }

    function delete() {
        $sql = "DELETE FROM cliente 
                WHERE       cliente_id = ?";
        $datos = [$this->cliente_id];
        consultar($sql, $datos);
    }

}


class ClienteView extends CommonView{

     function agregar($error=[]) {                                               
        settype($error, "array");                                               
        extract($_POST);                                                        
        $template = $this->get_rendered_template();                             
        $denominacion_usuario = $_SESSION['denominacion'] ?? "Invitado";        

		$keys = ['nif', 'calle', 'mumero','planta', 'puerta', 'email', 'ciudad', 
			'movil', 'denominacion', 'imagen', 'numero'];                                    

		foreach($keys as $key) {           
			if(!array_key_exists($key, $_POST)) $_POST[$key] = "";
			if(!array_key_exists("error_$key", $error)) $error["error_$key"] = "";
			${"ok_$key"} = Template::get_mark($$key, $error["error_$key"]); 
			$dict["{{$key}}"] = $$key;
			$dict["{ok_$key}"] = ${"ok_$key"};
			$dict["{error_$key}"] = $error["error_$key"];
        }                                                                       

        $dict = array_merge($dict, $error);                                     
        $file_cliente = PATH_MODULO_CLIENTE . '/cliente_agregar_form.html';     
        $form = Template::render_dict($file_cliente, $dict);                    
        print $this->render($form, $template);   
    }

    function ver($cliente) {
	$template = $this->get_rendered_template();

        $path = PATH_MODULO_CLIENTE . '/cliente_ver.html';
		$dict = [
            "{cliente_id}" => $cliente->cliente_id,
            "{denominacion}" => $cliente->denominacion,
            "{nif}" => $cliente->nif,
            "{calle}" => $cliente->domicilio->calle,
            "{numero}" => $cliente->domicilio->numero,
            "{planta}" => $cliente->domicilio->planta,
            "{puerta}" => $cliente->domicilio->puerta,
            "{ciudad}" => $cliente->domicilio->ciudad,
        ];
        $render_cliente = Template::render_dict($path, $dict);

        // dato de contacto 
        $html_datodecontacto = Template::extract( $path, 'datosdecontacto');
        $render_datodecontacto = [];
        foreach($cliente->datodecontacto_collection as $datodecontacto) {
            $dict = [
                "{denominacion_contacto}" => $datodecontacto->denominacion,
                "{valor}" => $datodecontacto->valor
            ];
            $render_datodecontacto[] = str_replace(
                array_keys($dict), 
                array_values($dict), 
                $html_datodecontacto
            );
        }
		$render_datodecontacto = implode(chr(10), $render_datodecontacto);
        $render_datodecontacto = str_replace(
            $html_datodecontacto, 
            $render_datodecontacto, 
            $render_cliente
        );

        // pedido
        $html_pedido = Template::extract($path, 'pedido');
        $render_pedido = [];
        foreach($cliente->pedido_collection as $pedido) {
            $dict = [
               "{pedido_id}" => $pedido->pedido_id, 
               "{fecha}" => $pedido->fecha, 
               "{estado}" => ESTADO_PEDIDO[$pedido->estado]
           ];
            $render_pedido[] = str_replace(
                array_keys($dict), 
                array_values($dict), 
                $html_pedido
            );
        }
		$render_pedido = implode(chr(10), $render_pedido);
        $render_pedido = str_replace(
            $html_pedido, 
            $render_pedido, 
            $render_datodecontacto
        );
		print $this->render($render_pedido, $template);
    }

    function editar($cliente, $error) {
		$template = $this->get_rendered_template();

        $path = PATH_MODULO_CLIENTE . '/cliente_editar.html';
        // imagen
        $html_cliente = file_get_contents($path);
        $html_imagen = Template::extraer($path, 'imagen');
        $file_exist = file_exists(PATH_IMAGES . "/cliente/{$cliente->cliente_id}");
        $dict = [
            "{recurso}" => ($file_exist) ? "eliminarImagen" : "imagen",
            "{imagen}" => ($file_exist) ? $cliente->cliente_id :"default.png",
            "{accion}" => ($file_exist) ? "Eliminar" : "Ver"
        ];
        $html_imagenes = str_replace(array_keys($dict), array_values($dict), $html_imagen);
		$html_cliente = str_replace($html_imagenes, $html_imagen, $html_cliente);
		$keys = ["error_denominacion", "error_nif", "error_calle", 
			"error_numero", "error_planta", "error_puerta",
		   	"error_ciudad", "error_email", "error_movil",
            "error_imagen"];
		foreach($keys as $k) {
			if(!array_key_exists($k, $error)) $error[$k] = "";
		}
		foreach($error as $key=>$value) {
			$error["{{$key}}"] = $value;
			unset($error[$key]);
		}
        $dict = [
            "{cliente_id}" => $cliente->cliente_id,
            "{domicilio_id}" => $cliente->domicilio->domicilio_id,
            "{denominacion}" => $cliente->denominacion,
            "{nif}"=> @$cliente->nif,
            "{calle}"=> @$cliente->domicilio->calle,
            "{numero}" => @$cliente->domicilio->numero,
            "{planta}" => @$cliente->domicilio->planta,
            "{puerta}" => @$cliente->domicilio->puerta,
            "{ciudad}" => @$cliente->domicilio->ciudad,

		];
		$dict = array_merge($dict, $error);
        $html_cliente = str_replace(
            array_keys($dict), 
            array_values($dict), 
            $html_cliente
        );
        // dato de contacto
        $html_datodecontacto = Template::extract($path, 'contacto');
        $render_contacto = [];
        foreach($cliente->datodecontacto_collection as $contacto) {
            $dict = [
                "{denominacion_contacto}" => $contacto->denominacion,
                "{valor}" => $contacto->valor,
                "{contacto_id}" => $contacto->datodecontacto_id
            ];
            $render_contacto[] = str_replace(
                array_keys($dict), 
                array_values($dict), 
                $html_datodecontacto
            );
		}
		$render_contacto = implode(chr(10), $render_contacto);
        $html_cliente = str_replace(
            $html_datodecontacto, 
            $render_contacto, 
            $html_cliente
        );
        print $this->render($html_cliente, $template);
    }

    function listar($clientes) {
		$template = $this->get_rendered_template();

        $path = PATH_MODULO_CLIENTE . '/cliente_listar.html';
        // clientes
        $html_row = Template::extract($path, 'table');
        $render_clientes = [];
        foreach($clientes as $cliente) {
            $dict = [
                "{cliente_id}" => $cliente->cliente_id,
                "{denominacion}" => $cliente->denominacion,
                "{nif}" => $cliente->nif,
                "{domicilio_id}" => $cliente->domicilio->domicilio_id,
                "{calle}" => $cliente->domicilio->calle,
                "{numero}" => $cliente->domicilio->numero,
                "{planta}" => $cliente->domicilio->planta,
                "{puerta}" => $cliente->domicilio->puerta,
                "{ciudad}" => $cliente->domicilio->ciudad
            ];

			$render_clientes[] = str_replace(
				array_keys($dict),
				array_values($dict),
				$html_row);
		}
		$render_clientes = implode(chr(10), $render_clientes);
		$html_table = file_get_contents($path);
        $html_table = str_replace($html_row, $render_clientes, $html_table);
		print $this->render($html_table, $template);
    }
    
}


class ClienteController {
    
    function __construct() {
        $this->model = new Cliente();
        $this->view = new ClienteView();
    }

    function __call($m, $a) { $this->listar(); }

    function agregar() {
        UsuarioHelper::check();
        $this->view->agregar();
    }
    
    function guardar() {        
        UsuarioHelper::check();
        extract($_POST);
        extract($_FILES);
        settype($numero, 'int');
        settype($planta, 'int');

        // validar formato NIF 
        $regex = '/^[0-9]{8,8}[A-Za-z]$/s'; // 12345678z
        $a = preg_match($regex, $nif, $coincidencias);

        // validar EMAIL
        $email = filter_var($email, FILTER_VALIDATE_EMAIL); 
        
        $error = [];
        if(!$coincidencias) $error['error_nif'] = ERR_NIF_FORMAT;                                                            
        if(!$email) $error['error_email'] =  ERR_EMAIL;                                                                     
        if(!$denominacion) $error['error_denominacion'] = ERR_DENOMINACION_EMPTY;
        if(!$calle) $error['error_calle'] = ERR_CALLE_EMPTY; 
        if(!($numero > 0)) $error['error_numero'] = ERR_NUMERO_EMPTY;
        if(!($planta > 0)) $error['error_planta'] = ERR_PLANTA_EMPTY;
        if(!$puerta) $error['error_puerta'] = ERR_PUERTA_EMPTY;
        if(!$ciudad) $error['error_ciudad'] = ERR_CIUDAD_EMPTY;
        if(!$movil) $error['error_movil'] = ERR_TELEFONO_EMPTY;
		
        // Validar el tipo MIME de las imágenes
        if(!in_array($imagen['type'], MIMES_PERMITIDOS) && $imagen['type']) {
             $error['{error_imagen}'] = ERR_IMAGE_TYPE;
        }

        if($error) exit($this->view->agregar($error));
        $nif = $coincidencias[0];
        
        // 1. guardar Compositor
        $this->model->domicilio->calle = $calle;
        $this->model->domicilio->numero = $numero;
        $this->model->domicilio->planta = $planta;
        $this->model->domicilio->puerta = $puerta;
        $this->model->domicilio->ciudad = $ciudad;
        $this->model->domicilio->insert();

        // 2. guardar objeto Compuesto
        $this->model->denominacion = $denominacion;
        $this->model->nif = $nif;
        $this->model->insert();

        // 3. guardar objeto Dependiente DatoDeContacto
        $dc = new DatoDeContactoController();
        $dc->guardar($this->model);

        $destino = PATH_IMAGES."/cliente/{$this->model->cliente_id}";
        move_uploaded_file($_FILES['imagen']['tmp_name'], $destino);
        
        header("Location: /cliente/ver/{$this->model->cliente_id}");
    }

    function ver($id=0) {
        UsuarioHelper::check();
        $this->model->cliente_id = (int) $id;
        $this->model->select();       
        $this->view->ver($this->model);
     }

     function editar($id=0, $error=[]) {
        UsuarioHelper::check();
        $this->model->cliente_id = (int) $id;
        $this->model->select();        
        $this->view->editar($this->model, $error);
     }

     function actualizar() {
        UsuarioHelper::check();
        extract($_POST);
        extract($_FILES);
        // SANEAR
        settype($numero, 'int');
        settype($planta, 'int');
        //VALIDAR

        // validar formato NIF 
        $regex = '/^[0-9]{8,8}[A-Za-z]$/s'; // 12345678z
        $a = preg_match($regex, $nif, $coincidencias);

        // validar EMAIL
        $email = $valor[0];
        $movil = $valor[1];
        $email = filter_var($email, FILTER_VALIDATE_EMAIL); 
        
        $error = [];
        if(!$coincidencias) $error['nif'] = ERR_NIF_FORMAT;                                                            
        if(!$email) $error['email'] =  ERR_EMAIL;                                                                     
        if(!$denominacion) $error['denominacion'] = ERR_DENOMINACION_EMPTY;
        if(!$calle) $error['calle'] = ERR_CALLE_EMPTY; 
        if(!($numero > 0)) $error['numero'] = ERR_NUMERO_EMPTY;
        if(!($planta > 0)) $error['planta'] = ERR_PLANTA_EMPTY;
        if(!$puerta) $error['puerta'] = ERR_PUERTA_EMPTY;
        if(!$ciudad) $error['ciudad'] = ERR_CIUDAD_EMPTY;
        if(!$movil) $error['movil'] = ERR_TELEFONO_EMPTY;

        // Validar el tipo MIME de las imágenes
        $mimes_permitidos = [
            'image/jpg',
            'image/png',
            'image/webp',
            'image/jpeg'
        ]; 
        if(!in_array($imagen['type'], $mimes_permitidos) && $imagen['type']) {
             $error['mime'] = ERR_IMAGE_TYPE;
        }

        if($error) exit($this->editar($cliente_id, $error));
        $nif = $coincidencias[0];
         
        $this->model->domicilio->calle = $calle;
        $this->model->domicilio->numero = $numero;
        $this->model->domicilio->planta = $planta;
        $this->model->domicilio->puerta = $puerta;
        $this->model->domicilio->ciudad = $ciudad;
        $this->model->domicilio->domicilio_id = $domicilio_id;
        $this->model->domicilio->update();
 
        $this->model->denominacion = $denominacion;
        $this->model->nif = $nif;
        $this->model->cliente_id = $cliente_id;
        $this->model->update();

        $datodecontacto = new DatoDeContactoController();
        $datodecontacto->actualizar($this->model);

        $destino = PATH_IMAGES."/cliente/{$this->model->cliente_id}";
        move_uploaded_file($_FILES['imagen']['tmp_name'], $destino);
        header("Location:/cliente/ver/{$this->model->cliente_id}");
     }

     function eliminar($id=0) {
         UsuarioHelper::check();
         $this->model->cliente_id = (int) $id;
         $this->model->delete();

         header('Location: /cliente/listar');
     }

     function listar() {
        UsuarioHelper::check();
        $coleccion = new Collector();
        @$coleccion->get("Cliente");
        $clientes = $coleccion->coleccion;
        
        $this->view->listar($clientes);
     }

    function imagen($id=0) {
        UsuarioHelper::check();
        $uri = rawurldecode($_SERVER['REQUEST_URI']);
        list($null, $modulo, $recurso, $cliente_id) = explode('/', $uri);
        $archivo = PATH_IMAGES."/cliente/$cliente_id" ;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if(!file_exists($archivo)) {
            $archivo = PATH_IMAGES."/cliente/default.png";
        }
        $mime = finfo_file($finfo, $archivo);
        finfo_close($finfo);
        header("Content-type: $mime");
        readfile($archivo);
    }

    function eliminarImagen($id=0) {
        UsuarioHelper::check();
        $uri = rawurldecode($_SERVER['REQUEST_URI']);
        list($null, $modulo, $recurso, $cliente_id) = explode('/', $uri); 
        $fichero = PATH_IMAGES."/cliente/$cliente_id" ;
        unlink($fichero);
        header("Location: /cliente/editar/{$id}");
    }

}


class ClienteDataHelper {

    static function get_denominacion($cliente_id) {
        $sql = "SELECT denominacion FROM cliente WHERE cliente_id = ?";
        $dato = [$cliente_id];
        $resultado = consultar($sql, $dato)[0];
        return @$resultado['denominacion'];
    }

    static function get_ultimos_clientes() {
		$sql = "SELECT 		cliente_id, denominacion 
				FROM 		cliente 
				ORDER BY 	cliente_id DESC LIMIT 5"; 
        $datos = [];
        return consultar($sql, $datos);
    }

}

?>
