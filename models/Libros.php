<?php

namespace app\models;

class Libros extends \yii\mongodb\ActiveRecord
{
    public static function collectionName()
    {
        return 'libros';
    }

    public function attributes()
    {
        // Devuelve un array con los nombres de los atributos
        return ['_id', 'titulo','autores','anio_publicacion','genero','descripcion','isbn'];
    }

    public function rules()
    {
        return [
         //   [['name'], 'required'],
            [['titulo'], 'string', 'max' => 255],
            [['autores'], 'string', 'max' => 255],
            [['anio_publicacion'], 'number'],
            [['genero'], 'string', 'max' => 255],
            [['descripcion'], 'string', 'max' => 255],
            [['isbn'], 'string', 'max' => 255],
           
         ];
    }

    public function fields()
    {
        return [
            'titulo',
            'autores',
            'anio_publicacion',
            'genero',
            'descripcion',
            'isbn'
        ];
    }

    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'titulo' => 'Titulo',
            'autores' => 'Autores',
            'anio_publicacion' => 'Anio_Publicacion',
            'genero' => 'Genero',
            'descripcion' => 'Descripcion',
            'isbn' => 'Isbn'
        ];
    }
}
