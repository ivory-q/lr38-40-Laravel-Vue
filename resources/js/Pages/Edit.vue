<template>
  <app-layout title="Shared">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight" ref="title">
        Edit {{ image.name }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-12">
          <div>
            <img
              :src="'photos/' + image.url"
              ref="image"
              class="h-[600px] w-full object-contain"
              alt=""
            />
          </div>
          <!-- Если мы не редактируем -->
          <div v-if="state == 'view'" class="mt-4">
            <!-- При нажатии состояние сменится на 'name' -->
            <jet-button @click="state = 'name'" class="mr-4">name</jet-button>
            <!-- При нажатии мы начнем обрезку картинки -->
            <jet-button @click="cropCurrent">crop</jet-button>
          </div>
          <!-- Если мы редактируем название -->
          <div v-else-if="state == 'name'" class="mt-4">
            <!-- Здесь мы вводим новое название картинки
                По умолчанию, оно заполняется текущим названием -->
            <jet-input
              id="name"
              class="mr-4"
              type="text"
              placeholder="Name"
              v-model="title"
              ref="image"
              required
              autofocus
            />
            <!-- Сохранить измененное название -->
            <jet-button @click="nameChange" class="mr-4">Save</jet-button>
            <!-- При нажатии состояние сменится на 'view' -->
            <jet-danger-button @click="state = 'view'"
              >Cancel</jet-danger-button
            >
          </div>
          <!-- Если мы обрезаем картинку -->
          <div v-else class="mt-4">
            <!-- Сохранить обрезку -->
            <jet-button @click="saveCrop" class="mr-4">Save</jet-button>
            <!-- Отменить обрезку -->
            <jet-danger-button @click="cancelCrop">Cancel</jet-danger-button>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import JetButton from "@/Jetstream/Button.vue";
import JetDangerButton from "@/Jetstream/DangerButton.vue";
import JetInput from "@/Jetstream/Input.vue";
import Cropper from "cropperjs";

export default {
  props: {
    // Картинка, передаваемая из галереи при нажатии кнопки
    image: Object,
  },
  data() {
    return {
      // Состояние, поскольку есть три четко выраженных состояни этого компонента
      //   мы будем использовать их для определения отображения элементов
      // state может быть 'view', 'name' и 'cropping'
      state: "view",
      // Название картинки, для отображения его в форме редактирования
      title: this.image.name,
    };
  },
  methods: {
    nameChange() {
      // Отправляем новое название по пути, указав id картинки
      axios
        .post("photos/api/photo/" + this.image.id, {
          name: this.title,
        })
        .then((res) => {
          // После получения ответа,
          //   меняем заголовок на значение input'a (новое название)
          this.$refs.title.innerText = "Edit " + this.title;
          // Меняем состояние на 'view'
          this.state = "view";
        });
    },
    cropCurrent() {
      // Меняем состояние на 'cropping'
      this.state = "cropping";
      // Инициализируем окно обрезки, указываем ему тег img, через ref
      this.cropper = new Cropper(this.$refs.image, {
        // Обрезка не может выходить за грани картинки
        viewMode: 2,
        // Нельзя двигать картинку
        movable: false,
        // Не показывать направляющие
        guides: false,
        // Не показывать крест в центре
        center: false,
        // Нельзя вращать картинку
        rotatable: false,
      });
    },
    saveCrop() {
      // Из окна обрезки мы получаем обрезанную картинку
      // Мы конвертируем данные в blob, для отправки запроса
      this.cropper.getCroppedCanvas().toBlob((blob) => {
        // Мы создаем данные формы и добавляем туда картинку
        let formData = new FormData();
        formData.append("photo", blob);
        // Мы посылаем запрос на изменение, указав id и новую картинку
        axios
          .post("photos/api/photo/" + this.image.id, formData)
          // После выполнения запроса, мы удаляем окно обрезки
          .then(this.cropper.destroy())
          // Меняем состояние на 'view'
          .then((this.state = "view"));
      });
    },
    cancelCrop() {
      // Мы удаляем окно обрезки
      this.cropper.destroy();
      // Меняем состояние на 'view'
      this.state = "view";
    },
  },
  components: {
    AppLayout,
    JetButton,
    JetDangerButton,
    JetInput,
  },
};
</script>

<style>
</style>
