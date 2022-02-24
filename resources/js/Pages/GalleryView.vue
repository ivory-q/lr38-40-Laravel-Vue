<template>
  <!-- Контейнер для картинок -->
  <div class="flex flex-wrap">
    <!-- Мы перебираем элементы в массиве source
            key - Этот атрибут позволяет Vue следить за изменениями
            динамически, как здесь, сгенерированных элементов -->
    <div v-for="image in source" :key="image.id" class="relative m-1 group">
      <!-- Если edit=true мы отображаем кнопки редактировать, удалить -->
      <div
        v-if="edit"
        class="absolute top-0 right-0 text-white hidden group-hover:block"
      >
        <!-- Здесь мы создаем кнопки редактирования и назначаем функции на их нажатия
            Как мы можем видеть, в эти функции передаются доп параметры,
            в edit мы передаем всю текущую картинку, а в delete только её id -->
        <button @click="editPhoto(image)" class="bg-blue-500 px-2">edit</button>
        <button @click="deletePhoto(image.id)" class="bg-red-500 px-2">
          delete
        </button>
      </div>
      <!-- Выводим название картинки поверх неё -->
      <div class="absolute bottom-0 left-0 bg-black text-white px-2">
        <h1>{{ image.name }}</h1>
      </div>
      <!-- Выводим саму картинку
        Все поля, такие как url и name опредляются структурой нашей БД
        Здесь используются кастомные свойства Tailwind [value]
        Он позволяет нам динамически передавать значения -->
      <img
        :src="'photos/' + image.url"
        :alt="image.name"
        class="h-[300px] w-[300px] object-cover"
      />
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  props: {
    // Источник картинок, именно в этот проп
    //      мы будем передавать картинки для их отображения
    // Здесь мы указываем имя пропа, а затем его тип данных,
    //      несколько типов данных, указываются в виде массива
    source: [FileList, Array],
    // Показывать ли меню редактирования/удаления
    // Здесь используется расширенный вариант,
    //      мы указываем не только тип, но и значение по умолчанию
    edit: { type: Boolean, default: false },
  },
  methods: {
    editPhoto(image) {
      // Вызываем метод рутера Inertia, для перехода по URL
      this.$inertia.post("/edit", { image: image });
    },
    deletePhoto(imgId) {
      // Посылаем delete запрос с переданным id картинки
      axios.delete("photos/api/photo/" + imgId);
      // Вызываем событие компонента, чтобы отреагировать на
      //    удаление картинки и обновить галерею
      this.$emit("imageDeleted");
    },
  },
};
</script>

<style>
</style>
