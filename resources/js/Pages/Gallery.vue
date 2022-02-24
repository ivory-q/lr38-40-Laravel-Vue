<template>
  <div class="p-12">
    <nav class="mb-8">
      <jet-button class="mr-2"> <Link href="/share">Share</Link></jet-button>
      <jet-button class="mr-2"> <Link href="/shared">Shared</Link></jet-button>
    </nav>
    <!--    form
        @submit.prevent - предотвращает отправку формы
        и заместо этого вызывает указанную функцию
            input
        @change - вызывает указанную функцию при изменении
        ref - ссылка на этот html элемент,
            работает так же как и getElemetById в простом js
            (не работает во Vue)
        multiple - html атрибут, позволяет загрузить несколько файлов
     -->
    <form @submit.prevent="uploadImage">
      <input
        @change="imageInputChanged"
        ref="upload"
        type="file"
        multiple
        name="photo"
        id="photo"
      />
      <jet-button type="submit">Send</jet-button>
    </form>
    <gallery-view
      class="mt-4"
      :source="photos"
      :edit="true"
      @imageDeleted="getImages"
    ></gallery-view>
  </div>
</template>

<script>
import { Link } from "@inertiajs/inertia-vue3";
import JetButton from "@/Jetstream/Button.vue";
import GalleryView from "@/Pages/GalleryView.vue";
import axios from "axios";

export default {
  data() {
    return {
      // здесь мы будем хранить фото,
      //   на данный момент выбранные в форме загрузки
      uploadPhotos: [],
      // Массив фотографий, который мы будем получать из БД
      //   и передавать в представление gallery-view
      photos: [],
    };
  },
  mounted() {
    this.getImages();
  },
  methods: {
    getImages() {
      // Получаем все фото, принадлежащие нам, и сохраняем их в переменную photos
      axios.get("photos/api/photo").then((res) => (this.photos = res.data));
    },
    uploadImage() {
      // Создаем объект FormData
      // Он хранит данные в том же виде, что и html форма
      let formData = new FormData();
      // В это массиве мы будем хранить наши запросы
      let requests = [];
      // Каждую загруженную картинку помещаем в formData,
      //  создаем запрос и храним его в массиве requests
      for (let i = 0; i < this.uploadPhotos.length; i++) {
        formData.append(
          "photo",
          this.uploadPhotos[i],
          this.uploadPhotos[i].name
        );
        requests.push(axios.post("photos/api/photo", formData));
      }
      // После наполнения массива запросов, мы выполняем их по очереди
      // Promise.all - следит за тем, чтобы все запросы выполнились успешно
      Promise.all(requests).then((res) => {
        // Если все запросы были выполнены успешно,
        // мы очищаем форму загрузки и вызываем метод getImages()
        this.$refs.upload.value = "";
        this.getImages();
      });
    },
    imageInputChanged() {
      // При выборе в форме картинок, мы присваиваем их в свойство uploadPhotos
      // this.$refs.upload - способ обращения к html элементу по его ref
      // выше, в форме, мы указали input'у значение ref="upload"
      this.uploadPhotos = this.$refs.upload.files;
    },
  },
  components: {
    Link,
    JetButton,
    GalleryView,
  },
};
</script>

<style>
</style>
