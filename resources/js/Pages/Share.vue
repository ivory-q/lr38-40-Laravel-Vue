<template>
  <app-layout title="Share">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Share</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-12">
          <!-- Если мы выбираем картинки (шаг 1) -->
          <div v-if="imageSelection">
            <h1 class="text-3xl mb-4">Select images to share</h1>
            <!-- Итерируем массив и выводим все картинки в виде списка -->
            <label
              v-for="photo in photos"
              :for="'check' + photo.id"
              :key="photo.id"
              class="grid grid-cols-3 items-center hover:bg-gray-100 border-b-2"
            >
              <img
                :src="'photos/' + photo.url"
                :alt="photo.name"
                class="h-[100px] w-[100px] object-cover"
              />
              <h1>{{ photo.name }}</h1>
              <!-- При отметке чекбокса, id картини помещается в selected -->
              <jet-checkbox
                @change="selectionChanged(selected, photo.id)"
                :id="'check' + photo.id"
              ></jet-checkbox>
            </label>

            <!-- Кнопка далее, меняет состояние -->
            <jet-button @click="imageSelection = false" class="mt-4"
              >Next</jet-button
            >
          </div>
          <!-- Если мы не выбираем картинки (шаг 2) -->
          <div v-else id="searchForm">
            <div>
              <!-- Форма поиска, привязана к searchQuery  -->
              <jet-label for="searchInp" value="Search" />
              <jet-input
                @input="searchUsers"
                id="searchInp"
                type="text"
                class="my-2 block w-full"
                v-model="searchQuery"
                required
                autofocus
              />
            </div>
            <!-- Контейнер результатов поиска -->
            <div class="h-[300px] overflow-y-auto">
              <!-- Результат поиска, при нажатии выбранные картинки поделятся с ним -->
              <jet-dropdown-link
                v-for="user in users"
                :key="user.phone"
                @click="shareImage(user.id)"
                >{{ user.first_name }} {{ user.surname }}</jet-dropdown-link
              >
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import JetButton from "@/Jetstream/Button.vue";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetCheckbox from "@/Jetstream/Checkbox.vue";
import JetDropdownLink from "@/Jetstream/DropdownLink.vue";
import axios from "axios";

export default {
  data() {
    return {
      // Пользователи, найденные по поисковому запросу
      users: [],
      // Все картинки, принадлежащие нам
      photos: [],
      // id выбранных из списка картинок
      selected: [],
      // Отслеживание состояния
      // Определяет выбираем мы картинки или пользователя
      imageSelection: true,
      // Поисковый запрос
      searchQuery: "",
    };
  },
  mounted() {
    this.searchUsers();
    this.getImages();
  },
  methods: {
    // array - массив, где искать
    // value - элемент, который добавить/удалить
    selectionChanged(array, value) {
      // Находим индекс элемента в массиве
      let index = array.indexOf(value);
      // Если элемента в массиве нет (индекс = -1)
      if (index === -1) {
        // Добавляем в массив значение
        array.push(value);
      } else {
        // Удаляем значение из массива
        array.splice(index, 1);
      }
    },
    getImages() {
      // Получаем все принадлежащие нам картинки из БД и храним их
      axios.get("photos/api/photo").then((res) => (this.photos = res.data));
    },
    shareImage(recieverId) {
      // Мы отправляем запрос со всеми выбранными id картинок
      //  а так же id их получателя (с кем мы ими делимся)
      axios
        .post("/photos/api/photo/user/" + recieverId + "/share", {
          photos: this.selected,
        })
        // После выполнения запроса, мы возвращаемся на главную страницу
        // Используем переход из кода с помощью метода рутера
        .then(this.$inertia.get("/dashboard"));
    },
    searchUsers() {
      // Мы получаем введеное в поиск значение и разделяем его по пробелам
      let searchQueries = this.searchQuery.split(" ");
      // Полученный массив мы разбиваем по значениям и отправляем
      axios
        .post("/photos/api/user", {
          first_name: searchQueries[0] || "",
          surname: searchQueries[1] || "",
          phone: searchQueries[2] || "",
        })
        .then((res) => {
          // После выполнения запроса, мы храним найденых пользователей в users
          this.users = res.data;
        });
    },
  },
  components: {
    AppLayout,
    JetButton,
    JetInput,
    JetLabel,
    JetCheckbox,
    JetDropdownLink,
  },
};
</script>

<style>
</style>
