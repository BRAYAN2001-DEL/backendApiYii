<?php
// ProductController.php
namespace app\controllers;
 
use Yii;
use yii\rest\ActiveController;
use yii\web\Response;
use app\models\Libros;
use yii\data\ArrayDataProvider;
use yii\mongodb\Query;
use Firebase\JWT\JWT;
use Firebase\JWT\Key; 


class LibrosController extends ActiveController
{
    public $modelClass = 'app\models\Libros';
    private $jwtSecret = 'zT+e0pYrT7P4w/5HZ5eWqQ=='; 

    public function actionCreate()
    {
        $model = new Libros();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($model->save()) {
            Yii::$app->response->statusCode = 201; // Created
            return ['status' => 'success', 'data' => $model];
        } else {
            Yii::$app->response->statusCode = 422; // Unprocessable Entity
            return ['status' => 'error', 'errors' => $model->errors];
        }
    }



public function actionGetLibrosByIdFromHeader()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $id = $request->headers->get('_id');

        if (!$id) {
            return ['status' => 'error', 'message' => 'El campo _id es requerido en el header.'];
        }

        $libros = Libros::findOne(['_id' => new \MongoDB\BSON\ObjectID($id)]);

        if (!$libros) {
            Yii::$app->response->statusCode = 404; // Not Found
            return ['status' => 'error', 'message' => 'Libros no encontrado.'];
        }

        return [
            '_id' => (string) $libros->_id,
            'titulo' => $libros->titulo,
            'autores' => $libros->autores,
        ];
    }

    public function actionGetLibrosByGeneroFromHeader()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $request = Yii::$app->request;
    $genero = $request->headers->get('genero');

    if (!$genero) {
        return ['status' => 'error', 'message' => 'El campo genero es requerido en el header.'];
    }

    $libros = Libros::find()->where(['genero' => $genero])->all();

    if (empty($libros)) {
        Yii::$app->response->statusCode = 404; // Not Found
        return ['status' => 'error', 'message' => 'Libros no encontrados para el género proporcionado.'];
    }

    $response = [];
    foreach ($libros as $libro) {
        $response[] = [
            '_id' => (string) $libro->_id,
            'titulo' => $libro->titulo,
            'autores' => $libro->autores,
            'genero' => $libro->genero,
        ];
    }

    return $response;
}



public function actionGetLibrosByGeneroAutoresAnioFromHeader()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $request = Yii::$app->request;
    $genero = $request->headers->get('genero');
    $autores = $request->headers->get('autores');
    $anioPublicacion = $request->headers->get('anio_publicacion');

    // Construir el arreglo de condiciones para la búsqueda
    $conditions = [];
    if ($genero) {
        $conditions['genero'] = $genero;
    }
    if ($autores) {
        $conditions['autores'] = $autores;
    }
    if ($anioPublicacion) {
        $conditions['anio_publicacion'] = $anioPublicacion;
    }

    // Realizar la búsqueda basada en las condiciones proporcionadas
    $libros = Libros::find()->where($conditions)->all();

    if (empty($libros)) {
        Yii::$app->response->statusCode = 404; // Not Found
        return ['status' => 'error', 'message' => 'No se encontraron libros para los criterios de búsqueda proporcionados.'];
    }

    $response = [];
    foreach ($libros as $libro) {
        $response[] = [
            '_id' => (string) $libro->_id,
            'titulo' => $libro->titulo,
            'autores' => $libro->autores,
            'genero' => $libro->genero,
            'anio_publicacion' => $libro->anio_publicacion,
        ];
    }

    return $response;
}


public function actionUpdateLibrosPut()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $request = Yii::$app->request;
    $bodyParams = $request->bodyParams;

    if (!isset($bodyParams['_id'])) {
        Yii::$app->response->statusCode = 400; // Bad Request
        return ['status' => 'error', 'message' => 'El campo _id es requerido'];
    }

    $id = $bodyParams['_id'];
    $libros = Libros::findOne(['_id' => new \MongoDB\BSON\ObjectID($id)]);

    if (!$libros) {
        Yii::$app->response->statusCode = 404; // Not Found
        return ['status' => 'error', 'message' => 'Libro no encontrado'];
    }

    if (isset($bodyParams['titulo'])) {
        $libros->titulo = $bodyParams['titulo'];
    }
    if (isset($bodyParams['autores'])) {
        $libros->autores = $bodyParams['autores'];
    }

    if ($libros->save()) {
        return ['status' => 'success', 'data' => $libros];
    } else {
        Yii::$app->response->statusCode = 422; // Unprocessable Entity
        return ['status' => 'error', 'errors' => $libros->errors];
    }
}




public function actionDelete($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (!$id) {
        Yii::$app->response->statusCode = 400; // Bad Request
        return ['status' => 'error', 'message' => 'El parámetro _id es requerido'];
    }

    $libros = Libros::findOne(['_id' => new \MongoDB\BSON\ObjectID($id)]);

    if (!$libros) {
        Yii::$app->response->statusCode = 404; // Not Found
        return ['status' => 'error', 'message' => 'libros no encontrado'];
    }

    if ($libros->delete()) {
        return ['status' => 'success', 'message' => 'libros eliminado correctamente']; 
    } else {
        Yii::$app->response->statusCode = 500; // Internal Server Error
        return ['status' => 'error', 'message' => 'Error al eliminar el producto'];
    }
}



// jwt

public function actionLogin()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $request = Yii::$app->request;
    $username = $request->post('username');
    $password = $request->post('password');

    $user = User::findOne(['username' => $username]);

    if ($user && Yii::$app->security->validatePassword($password, $user->password_hash)) {
        $payload = [
            'iss' => 'your_issuer',
            'aud' => 'your_audience',
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 300, // Expira en 5 minutos
            'uid' => $user->id,
        ];

        $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

        return ['token' => $jwt];
    } else {
        Yii::$app->response->statusCode = 401; // Unauthorized
        return ['status' => 'error', 'message' => 'Invalid username or password'];
    }
}



public function actionJwt()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $request = Yii::$app->request;
    $authHeader = $request->headers->get('Authorization');

    if (!$authHeader || !preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
        Yii::$app->response->statusCode = 401; // Unauthorized
        return ['status' => 'error', 'message' => 'No token provided'];
    }

    $jwt = $matches[1];

    try {
        $decoded = JWT::decode($jwt, new Key($this->jwtSecret, 'HS256'));

        // Si el token es válido, proceder a obtener el producto
        $id = $request->headers->get('_id');

        if (!$id) {
            return ['status' => 'error', 'message' => 'El campo _id es requerido en el header'];
        }

        $libros = Libros::findOne(['_id' => new \MongoDB\BSON\ObjectID($id)]);

        if (!$libros) {
            Yii::$app->response->statusCode = 404; // Not Found
            return ['status' => 'error', 'message' => 'Libro no encontrado'];
        }

        return [
            '_id' => (string) $libros->_id
            
        ];
    } catch (\Exception $e) {
        Yii::$app->response->statusCode = 401; // Unauthorized
        return ['status' => 'error', 'message' => 'Invalid token'];
    }
}


}