<?php
const DB_HOST = 'localhost';
const DB_NAME = 'crm';
const DB_USER = 'joseba';
const DB_PASS = 'joseba';

// ESTADOS DEL PEDIDO
const ESTADO_PEDIDO =  ["En espera", "En curso", "Enviado", "Entregado", "Devuelto"];
const BGC_ESTADO_PEDIDO = ["#ff00001a", "#f0e50166", "#00800033", "#01e8f066", "#f0ed0166"];
const COLOR_ESTADO_PEDIDO = ["red", "yellow", "green"," #01e8f0;", "#fffc00"];
// MIMES PERMITIDOS
const MIMES_PERMITIDOS = ['image/jpg', 'image/png', 'image/webp', 'image/jpeg']; 
// MENSAJES DE ERROR

//////////////// PRODUCTO /////////////////////
const ERR_NOT_OBJECT_EXIST = "Por favor, eliga o cree una categoría";
//////////////// PRECIO //////////////////////
const ERR_PRODUCT_PRICE_LT = "El valor de precio : %s debe ser mayor que 1";
const ERR_PRODUCT_PRICE_TOO_HIGHT = "El valor de precio : %s debe ser menor que 9999.99";
const ERR_PRODUCT_PRICE_IS_EMPTY = "Campo requerido";
//////////////// DENOMINACIÓN //////////////////////
const ERR_PRODUCT_DENOMINACION_IS_EMPTY = "Campo requerido";
//////////////// CLIENTE //////////////////////
const ERR_NIF_FORMAT = "El DNI no es válido";
const ERR_EMAIL = "El email introducido no es válido";
const ERR_DENOMINACION_EMPTY = "El campo nombre es requerido";
const ERR_CALLE_EMPTY = "El campo calle es requerido";
const ERR_NUMERO_EMPTY = "El campo número es requerido";
const ERR_PUERTA_EMPTY = "El campo puerta es requerido";
const ERR_PLANTA_EMPTY = "El campo planta es requerido";
const ERR_CIUDAD_EMPTY = "El campo ciudad es requerido";
const ERR_TELEFONO_EMPTY = "El campo teléfono es requerido";
const ERR_PISO_CHARACTER = "Caracteres no válidos en el campo";
const ERR_NUMERO_CHARACTER = "Caracteres no válidos en el campo";
//////////////// IMÁGENES  //////////////////////
const ERR_IMAGE_TYPE = "Formato de imágen no válido";
//////////////// CATEGORIA /////////////////////
const ERR_CATEGORY_INPUT_IS_EMPY = "Campo requerido";
//////////////// USUARIO  /////////////////////
const ERR_PASS_LONG_IS_TOO_SORT = "La contraseña debe de tener al menos 8 caracteres";
const ERR_PASS_INPUT_EMPTY = "El campo es requerido";
const ERR_DENOMINACION_INPUT_EMPTY = "El campo es requerido";
const ERR_PASS_CTYPE = "La contraseña debe de contener caracteres en mayusculas y minúsculas y caracteres especiales ($,&..)";


// RUTA ABSOLUTA DE LA CARPETA PARA LAS IMÁGENES
const PATH_IMAGES = "/home/joseba/proyectos/eugeniabahit/private";
// RUTA ABSOLUTA PARA LAS CREDENCIALES
const PATH_CREDENCIALES = "/home/joseba/proyectos/eugeniabahit/credenciales"; 
// RUTA PARA LOS MÓDULOS
const PATH_MODULO_CLIENTE = "static/modulos/cliente";
const PATH_MODULO_DOMICILIO = "static/modulos/domicilio";
const PATH_MODULO_CATEGORIA = "static/modulos/categoria";
const PATH_MODULO_USUARIO = "static/modulos/usuario";
const PATH_MODULO_PRODUCTO = "static/modulos/producto";
const PATH_MODULO_PEDIDO = "static/modulos/pedido";
?>
