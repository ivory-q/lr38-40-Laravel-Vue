<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Request $request – наш запрос, мы передаем его в функцию,
    //  чтобы получить доступ к переданным в нем данным
    public function store(Request $request)
    {
        // Здесь мы проверям правильность введенных данных,
        //  согласно указанным требованиям
        // $request->all() получает все переданные переменные в теле за-проса
        // например из формы html
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'surname' => 'required',
            // phone (обязательный, уникальный по таблице users,
            //  состоит из одиннадцати чисел)
            'phone' => 'required|unique:users|digits:11',
            'password' => 'required',
        ]);
        // Если валидация не проходит, то возращается ответ с ошибкой и кодом 442
        if ($validator->fails()) {
            return response()->json($validator->errors(), 442);
        }
        // Если все успешно, то мы создаем новую запись в таблице users с помо-щью модели User
        // Все данные берутся из тела запроса
        // Пароль сохраняется в БД в виде хэша
        $data = User::create([
            'first_name' => $request['first_name'],
            'surname' => $request['surname'],
            'phone' => $request['phone'],
            'password' => Hash::make($request['password']),
        ]);
        // При успешном выполнении мы возвращаем id нового пользователся с кодом 201 Created
        return response()->json(["Code" => $data->id], 201);
    }

    public function enter(Request $request)
    {
        // Выполняется такая же проверка, как и в store
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:11',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 442);
        }
        // Успешно проверенные данные мы помещаем в переменную credentials
        $credentials = $validator->validated();
        // Мы пытаемся авторизоваться с этими данными с функцией attempt
        // Если авторизация проходит успешно, то условие выполнятеся
        // Далее, мы находим пользователя в БД по полю phone
        //  через функцию where модели User и выбираем первую запись
        // Мы создаем авторизационный токен, который впоследствии будет
        //  использоватся для авторизации наших запросов от имени созданного пользователя
        //  и возвращаем его в виде ответа
        if (Auth::attempt($credentials)) {
            $user = User::where('phone', $request->phone)->first();
            $token = $user->createToken('user-token');
            return $token->plainTextToken;
        }
        // Если авторизация не прошла, то мы возвращаем сообщение об ошибке с кодом 404 Not Found
        return response()->json(["login" => "Incorrect login or password"], 404);
    }

    public function show(Request $request)
    {
        // Мы сначала выбираем всех пользователей кроме текущего
        // auth()->user()->id возвращает id текущего авторизованного пользо-вателя
        // Затем из этих записей мы выбираем те, где поле first_name похоже
        //  на переданное нами значение в переменной first_name формы
        //  Мы делаем это с помощью SQL оператора LIKE, % в строке указывает на другие символы,
        //  в данном случае мы указали, что оно может находится в середине строки, а surname и phone только в начале
        // Get() получает все найденные записи в виде массива(колллекции Laravel)
        $search = User::where('id', '!=', auth()->user()->id)->where('first_name', "LIKE", "%$request->first_name%")
            ->where('surname', "LIKE", "{$request->surname}%")
            ->where('phone', "LIKE", "{$request->phone}%")
            ->get();
        // Если мы нашли записи по нашему запросу, то мы возвращаем их с кодом 200 Success
        // Иначе, мы возвращаем пустой массив
        if ($search) {
            return response()->json($search, 200);
        } else {
            return response()->json([], 200);
        }
    }

    public function logout(Request $request)
    {
        // Мы удаляем токен текущего пользователя,
        //  после чего мы не сможем использовать его для авторизации запросов
        $request->user()->tokens()->delete();
        return response()->json([], 200);
    }
}
