<template>
  <app-layout title="Shared">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Shared</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-12">
          <!-- Управление, смотреть чем поделились мы или другие с нами -->
          <nav class="mb-8">
            <!-- Поделились мы
            Меняется состояние и выводятся наши картинки -->
            <jet-button
              @click="
                byMe = true;
                getMine();
              "
              class="mr-2"
              >by Me</jet-button
            >
            <!-- Поделились с нами
            Меняется состояние и выводятся картинки других -->
            <jet-button
              @click="
                byMe = false;
                getOthers();
              "
              class="mr-2"
              >from Others</jet-button
            >
          </nav>
          <!-- Динамичный заголовок, меняется от состояния -->
          <h1 class="text-3xl">{{ byMe ? "by Me" : "from Others" }}</h1>
          <div>
            <!-- Галерея картинок, передаем полученные результаты -->
            <div>
              <gallery-view class="mt-4" :source="shared"></gallery-view>
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
import GalleryView from "@/Pages/GalleryView.vue";
import axios from "axios";

export default {
  data() {
    return {
      // Поделились мы или поделились с нами
      byMe: false,
      // Массив фотографий, будем передавать его в gallery-view
      shared: [],
    };
  },
  mounted() {
    this.getOthers();
  },
  methods: {
    getMine() {
      // Очищаем массив картинок, галерея тоже очистится
      this.shared = [];
      // Отправляем запрос
      axios.post("/photos/api/photo/user/shared").then((res) => {
           // Полученные данные присваиваем массиву картинок
        this.shared = res.data;
      });
    },
    getOthers() {
      // Очищаем массив картинок, галерея тоже очистится
      this.shared = [];
      // Отправляем запрос с id нашего пользователя
      axios
        .post("/photos/api/photo/user/" + this.$page.props.user.id + "/share")
        .then((res) => {
          // Полученные данные присваиваем массиву картинок
          this.shared = res.data;
        });
    },
  },
  components: {
    AppLayout,
    JetButton,
    GalleryView,
  },
};
</script>

<style>
</style>
