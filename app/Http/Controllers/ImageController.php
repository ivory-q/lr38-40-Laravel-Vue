<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ImageController extends Controller
{


    public function index()
    {
        // Мы находим все картинки, которые принадлежат текущему пользователю
        $images = Image::where('owner_id', auth()->user()->id)->get();

        //  и возвращаем их в виде json с кодом 200
        return response()->json($images, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 442);
        }
        // Создаем уникальный id, который будет добавлен к имени картинки
        //  чтобы исключить возможность столкновения и перезаписи файлов с
        //  одинаковыми именами
        // time() - возвращает текущее время в миллисекундах
        // uniqid() - генерирует уникальный id
        $uniqueId =  time() . uniqid() . "_";
        // Из запроса мы получаем переданную через запрос картинку
        //  и получаем её изначальное название файла
        $photoName = $request->file("photo")->getClientOriginalName();
        // Мы перемещаем картинку из запроса в public/photos,
        //  добавив к её имени, в начало, наш уникальный идентификатор
        //  public_path() - возвращает абсолютный путь до папки public Laravel
        //  например  C://openserver/domains/lr38/public
        $request->file("photo")->move(public_path("/photos/"), $uniqueId . $photoName);
        // Мы создаем запись о картике в БД,
        //  указав в заполняемых свойствах её имя и id владельца(текущего пользователя)
        Image::create(["url" => $uniqueId . $photoName, "owner_id" => auth()->user()->id]);
        // Возвращаем ответ с успешом и кодом 201
        return response()->json(["message" => "Successfull creation"], 201);
    }

    public function update(Request $request, $id)
    {
        // Мы находим картинку по переданному в URL id
        $imageById = Image::where('id', $id)->first();
        // Проверяем, являемся ли мы её владельцем
        if (auth()->user()->id != $imageById->owner_id) {
            return response()->json([], 403);
        }
        $validator = Validator::make($request->all(), [
            // Указывает, что мы должны использовать метод PATCH
            // Он сам превращает в него изначальный метод POST
            // PATCH - Http медтод для обновления ресурсов, в отличие
            //  от PUT, он не содержит сам ресурс, а лишь иструкции по его модификации
            '_method' => "patch",
            'name',
            'photo'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 442);
        }
        // Если в запросе мы передаем новую картинку, то оно заменяется
        if ($request->photo) {
            // Создаем уникальное имя для файла
            $uniqueId =  time() . uniqid() . "_";
            // Удаляем старую картинку, используя её имя из БД
            unlink(public_path("/photos/") . $imageById->url);
            // Загружем новую, так же как и в store
            $newPhotoName = $uniqueId . $request->file("photo")->getClientOriginalName();
            $request->file("photo")->move(public_path("/photos/"), $newPhotoName);
            // Обновляем данные записи с помощью метода update модели Image
            // Мы указываем имя нового файла и id текущего пользователя как владельца картинки
            $imageById->update(["url" => $newPhotoName, "owner_id" => auth()->user()->id]);
        }
        // Если в запросе мы передали новое название для картинки
        if ($request->name) {
            // Обновляем в записи название картинки
            $imageById->update(["name" => $request->name]);
        }
        // Возвращаем успех 200 Succeess
        return response()->json([], 200);
    }

    public function show($id)
    {
        // Мы передаем параметр id в url при переходе
        //  laravel автоматически считывает его из url и
        //  передает его в соответствующую функцию контроллера
        // Мы ищем из картинок, принадлежащих нам, картинку с этим id
        //  и возвращаем её
        $images = Image::where('owner_id', auth()->user()->id)
            ->where('id', $id)->get();

        return response($images);
    }

    public function destroy($id)
    {
        // Находим картинку по id
        $imageById = Image::where('id', $id)->first();
        // Проверяем владеем ли мы этой картинкой
        if (auth()->user()->id != $imageById->owner_id) {
            return response()->json([], 403);
        }
        // Удаляем картинку из папки
        unlink(public_path("/photos/") . $imageById->url);
        // Удаляем картинку из БД
        $imageById->delete();
        // Возвращаем ответ 204 Deleted
        return response()->json([], 204);
    }

    public function share(Request $request, $id)
    {
        // Получаем id текущего пользователя
        $userId = auth()->user()->id;
        // По этому пути мы передаем массив картинок, которыми надо поделиться
        //  с пользователем чей id мы передаем в URL
        // Здесь мы, с помощью функции collect, преобразуем массив в коллекцию Laravel
        // Далее мы передираем все элементы (id картинок) вызывая для них функцию с помощью each
        // Перед телом функции мы можем видеть директиву use.
        // Поскольку в большинстве языков программирования существует такой концепт, как область видимости
        // (https://habr.com/ru/post/517338/),
        //  мы не сможем использовать переменные, определенные вне анонимной функции, вызываемой each.
        // Чтобы все же передать внешние переменные мы указываем их, используя директиву use
        collect($request->photos)->each(function ($photo) use ($id, $userId) {
            $imageById = Image::where('id', $photo)->first();
            if ($userId != $imageById->owner_id) {
                return response()->json([], 403);
            }
            // Поскольку в модели Image в поле casts мы указали Laravel, чтобы он
            //  преобразовывал поле users в массив, мы можем выполнять на нем методы массива
            // Тут мы проверяем, находится ли уже id пользователя, с которым мы хотим поделится
            //  в массиве, чтобы не поделить одну картинку несколько раз одному поьзователю
            if (in_array($id, $imageById->users)) {
                // Если мы уже поделились этой картинкой с указанным пользователем,
                //  то мы прерываем дальнейшее выполнение функции контроллер
                //  просто вернув значение true
                return true;
            }
            // Если мы ещё не делились картинкой с этим пользователем, то мы добавляем его в массив users
            // Здесь мы сначала конвертируем переданный id в число, поскольку id пользователей хранятся в таком виде
            // Затем мы раскрываем массив с id остальных пользователей, с которыми уже поделились
            // $id = "2" строка        (int)$id = 2 число
            // $imageById->users = [1,2,3] массив       ...$imageById->users = 1,2,3 просто значения
            // $id="2"    $imageById->users=[1,3]     [...$imageById->users, (int)$id]]=[1,2,3]
            Image::where('id', $photo)->update(['users' => [...$imageById->users, (int)$id]]);
        });

        // По заданию, в качестве ответа по этому пути, мы должны вывести все картинки
        //  которыми поделились с указанным пользователем
        // Здесь мы используем удобную функцию whereJsonContains, которая сама ищет
        //  в массиве users указанное значение
        $existingPhotos = Image::whereJsonContains('users', (int)$id)->get();
        return response()->json($existingPhotos, 200);
    }

    public function shared()
    {
        // Получаем id текущего пользователя
        $userId = auth()->user()->id;
        // Среди наших фотографий ищем те, в которых массив users не пустой
        // Если он не пустой - значит мы им поделились
        // Для филтра мы используем SQL операнды NOT и LIKE
        $sharedPhotos = Image::where("owner_id", $userId)
            ->where('users', 'NOT LIKE', "[]")->get();
        // Мы получаем найденные результаты и возвращаем их
        return response()->json($sharedPhotos, 200);
    }

    public function edit(Request $request)
    {
        // Передает ему данные и рендерит компонент Vue
        // Таким образом при переходе на /edit, мы передаем картинку
        //  в компонент Edit и сами на него переходим
        return Inertia::render("Edit", ['image' => $request->image]);
    }
}
