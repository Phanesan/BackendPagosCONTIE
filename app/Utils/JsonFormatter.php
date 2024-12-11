<?php
/*
Funciones de formateo de JSON para respuestas de la API
*/

namespace App\Utils;

class JsonFormatter
{
    /* Codigos de estado
    * 0: OK
    * 1: OK con errores
    */
    static function successFormatJson($message, $code, $data)
    {
        if($data == null || $data == '' || $data == []) {
            return response()->json([
                'message' => $message,
                'code' => $code
            ]);
        }
        return response()->json([
            'message' => $message,
            'code' => $code,
            'data' => $data
        ]);
    }

    /* Codigos de estado
    * -1: Error inesperado
    * -2: Error de validacion
    * -3: Error de recurso no encontrado
    * -4: Error de campo unico
    */
    static function errorFormatJson($message, $code, $errors)
    {
        if($errors != null || $errors != '' || $errors != []) {
            return response()->json([
                'message' => $message,
                'code' => $code,
                'errors' => $errors
            ]);
        }
        return response()->json([
            'message' => $message,
            'code' => $code
        ]);
    }
}
