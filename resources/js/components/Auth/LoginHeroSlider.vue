<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';

type Slide = {
  title: string;
  description: string;
  image: string;
};

const props = withDefaults(defineProps<{
  slides: Slide[];
  interval?: number;
}>(), {
  interval: 4500,
});

const current = ref(0);
const dots = computed(() => props.slides.map((_, index) => index));

const showSlide = (index: number) => {
  current.value = index;
};

const nextSlide = () => {
  current.value = (current.value + 1) % props.slides.length;
};

let timer: ReturnType<typeof setInterval> | null = null;
const start = () => {
  stop();
  if (props.slides.length > 1) {
    timer = setInterval(nextSlide, props.interval);
  }
};
const stop = () => {
  if (timer) {
    clearInterval(timer);
    timer = null;
  }
};

onMounted(start);
onUnmounted(stop);
</script>

<template>
  <div class="flex flex-1 flex-col items-center justify-center text-center lg:items-start lg:text-left">
    <div class="max-w-md">
      <div v-for="(slide, index) in slides" :key="index" :class="index === current ? '' : 'hidden'" class="space-y-6">
        <!-- Image commented out for now -->
        <!-- <img :src="slide.image" alt="Login slide" class="mx-auto w-[230px] sm:w-[290px] lg:w-[320px] object-contain" /> -->
        <div>
          <h2 class="text-2xl sm:text-3xl font-bold">
            {{ slide.title }}
          </h2>
          <p class="mt-3 text-sm sm:text-base text-white/90">
            {{ slide.description }}
          </p>
        </div>
      </div>

      <div class="mt-6 flex items-center justify-center gap-2 lg:justify-start">
        <button v-for="dot in dots" :key="dot" @click="showSlide(dot)"
          :class="dot === current ? 'h-2 w-6 rounded-full bg-white' : 'h-2 w-2 rounded-full bg-white/50'"
          class="transition-all" type="button"></button>
      </div>
    </div>
  </div>
</template>
