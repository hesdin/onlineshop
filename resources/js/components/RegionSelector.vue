<script setup lang="ts">
import { useIndonesiaRegions } from '@/composables/useIndonesiaRegions';
import { Button } from '@/components/ui/button';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from '@/components/ui/command';
import { Label } from '@/components/ui/label';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { Check, ChevronsUpDown } from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

const props = defineProps<{
  provinceId: number | string | null;
  cityId: number | string | null;
  districtId?: number | string | null;
  showDistrict?: boolean;
  disabled?: boolean;
  provincesProp?: RegionOption[];
  citiesProp?: RegionOption[];
  districtsProp?: RegionOption[];
  errors?: {
    provinceId?: string;
    cityId?: string;
    districtId?: string;
  };
  provinceRequired?: boolean;
  cityRequired?: boolean;
  districtRequired?: boolean;
  initialProvinceName?: string | null;
  initialCityName?: string | null;
  initialDistrictName?: string | null;
}>();

const emit = defineEmits(['update:provinceId', 'update:cityId', 'update:districtId']);

const {
  provinces,
  cities,
  districts,
  loadProvinces,
  loadCities,
  loadDistricts,
  findProvinceByName,
  findCityByName,
  findDistrictByName,
} = useIndonesiaRegions();

const provinceOpen = ref(false);
const cityOpen = ref(false);
const districtOpen = ref(false);
const provinceCommandRef = ref<any>(null);
const cityCommandRef = ref<any>(null);
const districtCommandRef = ref<any>(null);

const normalizeIdValue = (value?: string | number | null) => {
  if (value === undefined || value === null) {
    return null;
  }

  if (typeof value === 'number') {
    return Number.isNaN(value) ? null : String(value);
  }

  const trimmed = value.trim();
  return trimmed.length ? trimmed : null;
};

const isSameId = (first?: string | number | null, second?: string | number | null) =>
  normalizeIdValue(first) === normalizeIdValue(second);

const selectedProvinceId = ref<number | string | null>(null);
const selectedCityId = ref<number | string | null>(null);
const selectedDistrictId = ref<number | string | null>(null);

const selectedProvinceName = computed(() => {
  const province = findProvince(selectedProvinceId.value);
  if (province) {
    return province.name;
  }

  return props.initialProvinceName ?? '';
});

const selectedCityName = computed(() => {
  const city = findCity(selectedCityId.value);
  if (city) {
    return city.name;
  }

  return props.initialCityName ?? '';
});

const selectedDistrictName = computed(() => {
  const district = findDistrict(selectedDistrictId.value);
  if (district) {
    return district.name;
  }

  return props.initialDistrictName ?? '';
});

const ensureProvincesLoaded = async () => {
  if (!provinces.value.length) {
    if (props.provincesProp?.length) {
      provinces.value = props.provincesProp;
      return;
    }
    await loadProvinces();
  }
};

const resetCityAndDistrict = () => {
  selectedCityId.value = null;
  selectedDistrictId.value = null;
  cities.value = [];
  districts.value = [];
  emit('update:cityId', null);
  emit('update:districtId', null);
};

const resetDistrict = () => {
  selectedDistrictId.value = null;
  districts.value = [];
  emit('update:districtId', null);
};

const findProvince = (id?: number | string | null) => {
  const target = normalizeIdValue(id);
  if (!target) {
    return null;
  }
  return provinces.value.find((item) => normalizeIdValue(item.id) === target) ?? null;
};

const findCity = (id?: number | string | null) => {
  const target = normalizeIdValue(id);
  if (!target) {
    return null;
  }
  return cities.value.find((item) => normalizeIdValue(item.id) === target) ?? null;
};

const findDistrict = (id?: number | string | null) => {
  const target = normalizeIdValue(id);
  if (!target) {
    return null;
  }
  return districts.value.find((item) => normalizeIdValue(item.id) === target) ?? null;
};

const ensureCitiesForProvince = async (province?: RegionOption | null) => {
  if (!province?.code) {
    cities.value = [];
    return;
  }

  if (props.citiesProp?.length) {
    cities.value = props.citiesProp.filter((c) => c.province_code === province.code);
  } else {
    await loadCities(province.code);
  }
};

const ensureDistrictsForCity = async (city?: RegionOption | null) => {
  if (!props.showDistrict || !city?.code) {
    districts.value = props.showDistrict ? districts.value : [];
    return;
  }

  if (props.districtsProp?.length) {
    districts.value = props.districtsProp.filter((d) => d.city_code === city.code);
  } else {
    await loadDistricts(city.code);
  }
};

watch(
  () => props.provinceId,
  async (value) => {
    await ensureProvincesLoaded();

    if (!normalizeIdValue(value)) {
      if (selectedProvinceId.value) {
        selectedProvinceId.value = null;
        resetCityAndDistrict();
      }
      return;
    }

    const province = findProvince(value);
    if (!province) {
      selectedProvinceId.value = null;
      resetCityAndDistrict();
      return;
    }

    selectedProvinceId.value = province.id;
    await ensureCitiesForProvince(province);
  },
  { immediate: true },
);

watch(
  () => props.cityId,
  async (value) => {
    if (!normalizeIdValue(value)) {
      if (selectedCityId.value) {
        selectedCityId.value = null;
        resetDistrict();
      }
      return;
    }

    await ensureProvincesLoaded();

    const province = findProvince(selectedProvinceId.value ?? props.provinceId ?? null);
    if (province) {
      await ensureCitiesForProvince(province);
    }

    const city = findCity(value);
    if (!city) {
      selectedCityId.value = null;
      resetDistrict();
      return;
    }

    selectedCityId.value = city.id;
    await ensureDistrictsForCity(city);
  },
  { immediate: true },
);

watch(
  () => props.districtId,
  async (value) => {
    if (!props.showDistrict) {
      return;
    }

    if (!normalizeIdValue(value)) {
      if (selectedDistrictId.value) {
        selectedDistrictId.value = null;
      }
      return;
    }

    await ensureProvincesLoaded();

    const province = findProvince(selectedProvinceId.value ?? props.provinceId ?? null);
    if (province) {
      await ensureCitiesForProvince(province);
    }
    const city = findCity(selectedCityId.value ?? props.cityId ?? null);
    if (city) {
      await ensureDistrictsForCity(city);
    }

    const district = props.showDistrict ? findDistrict(value) : null;
    if (district) {
      selectedDistrictId.value = district.id;
    }
  },
  { immediate: true },
);

watch(
  selectedProvinceId,
  async (id, previous) => {
    if (props.disabled || isSameId(id, previous)) {
      return;
    }
    if (isSameId(id, props.provinceId)) {
      const province = findProvince(id);
      if (province) {
        await ensureCitiesForProvince(province);
      }
      return;
    }

    const province = findProvince(id);
    emit('update:provinceId', province?.id ?? null);
    resetCityAndDistrict();

    if (province?.code) {
      await ensureCitiesForProvince(province);
    }
  },
);

watch(
  selectedCityId,
  async (id, previous) => {
    if (props.disabled || isSameId(id, previous)) {
      return;
    }
    if (isSameId(id, props.cityId)) {
      const city = findCity(id);
      if (city) {
        await ensureDistrictsForCity(city);
      }
      return;
    }

    const city = findCity(id);
    emit('update:cityId', city?.id ?? null);
    resetDistrict();

    if (props.showDistrict && city?.code) {
      await ensureDistrictsForCity(city);
    }
  },
);

watch(selectedDistrictId, (id, previous) => {
  if (!props.showDistrict || props.disabled || isSameId(id, previous)) {
    return;
  }

  if (isSameId(id, props.districtId)) {
    return;
  }

  const district = props.showDistrict ? findDistrict(id) : null;
  emit('update:districtId', district?.id ?? null);
});

onMounted(async () => {
  if (props.provincesProp?.length) {
    provinces.value = props.provincesProp;
  }
  if (props.citiesProp?.length) {
    cities.value = props.citiesProp;
  }
  if (props.districtsProp?.length) {
    districts.value = props.districtsProp;
  }

  await ensureProvincesLoaded();

  if (props.provinceId) {
    const province = findProvince(props.provinceId);
    if (province) {
      selectedProvinceId.value = province.id;
      await loadCities(province.code);
    }
  }

  if (props.cityId && selectedProvinceId.value) {
    const city = findCity(props.cityId);
    if (city) {
      selectedCityId.value = city.id;
      if (props.showDistrict) {
        await loadDistricts(city.code);
      }
    }
  }

  if (props.showDistrict && props.districtId && selectedCityId.value) {
    const district = findDistrict(props.districtId);
    if (district) {
      selectedDistrictId.value = district.id;
    }
  }
});

const renderButtonText = (text: string, fallback: string) => text || fallback;

const setProvince = async (province?: RegionOption | null) => {
  selectedProvinceId.value = province?.id ?? null;
  emit('update:provinceId', province?.id ?? null);
  resetCityAndDistrict();
  await ensureCitiesForProvince(province ?? findProvince(selectedProvinceId.value));
  provinceOpen.value = false;
};

const setCity = async (city?: RegionOption | null) => {
  selectedCityId.value = city?.id ?? null;
  emit('update:cityId', city?.id ?? null);
  resetDistrict();
  await ensureDistrictsForCity(city ?? findCity(selectedCityId.value));
  cityOpen.value = false;
};

const setDistrict = (district?: RegionOption | null) => {
  selectedDistrictId.value = district?.id ?? null;
  emit('update:districtId', district?.id ?? null);
  districtOpen.value = false;
};

const focusFirstCommandItem = (commandRef: { value?: any }) => {
  const root = commandRef?.value?.$el ?? commandRef?.value ?? null;
  if (!root) return;
  const target =
    root.querySelector?.('[data-command-item]') ||
    root.querySelector?.('input, [tabindex]');
  if (target) {
    (target as HTMLElement).focus();
  }
};

watch(provinceOpen, (open) => {
  if (!open) return;
  nextTick(() => focusFirstCommandItem({ value: provinceCommandRef.value }));
});

watch(cityOpen, (open) => {
  if (!open) return;
  nextTick(() => focusFirstCommandItem({ value: cityCommandRef.value }));
});

watch(districtOpen, (open) => {
  if (!open) return;
  nextTick(() => focusFirstCommandItem({ value: districtCommandRef.value }));
});

const handleTriggerKeydown = (event: KeyboardEvent, type: 'province' | 'city' | 'district') => {
  if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') {
    return;
  }

  event.preventDefault();

  if (type === 'province') {
    if (props.disabled) return;
    provinceOpen.value = true;
    return;
  }

  if (type === 'city') {
    if (props.disabled || !selectedProvinceId.value) return;
    cityOpen.value = true;
    return;
  }

  if (type === 'district') {
    if (props.disabled || !selectedCityId.value) return;
    districtOpen.value = true;
  }
};
</script>

<template>
  <div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-2">
      <Label class="flex items-center gap-1">
        Provinsi
        <span v-if="props.provinceRequired" class="text-red-500">*</span>
      </Label>
      <Popover v-model:open="provinceOpen">
        <PopoverTrigger as-child>
          <Button variant="outline" role="combobox" class="w-full justify-between" :disabled="disabled"
            @keydown="handleTriggerKeydown($event, 'province')">
            {{ renderButtonText(selectedProvinceName, 'Pilih provinsi') }}
            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
          </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[280px] p-0">
          <Command ref="provinceCommandRef">
            <CommandInput placeholder="Cari provinsi..." />
            <CommandEmpty>Provinsi tidak ditemukan.</CommandEmpty>
            <CommandGroup>
              <CommandItem v-for="province in provinces" :key="province.id" :value="province.name"
                @select="() => setProvince(province)">
                <Check class="mr-2 h-4 w-4"
                  :class="isSameId(selectedProvinceId, province.id) ? 'opacity-100' : 'opacity-0'" />
                {{ province.name }}
              </CommandItem>
            </CommandGroup>
          </Command>
        </PopoverContent>
      </Popover>
      <p v-if="errors?.provinceId" class="text-xs text-red-600">
        {{ errors.provinceId }}
      </p>
    </div>

    <div class="space-y-2">
      <Label class="flex items-center gap-1">
        Kota/Kabupaten
        <span v-if="props.cityRequired" class="text-red-500">*</span>
      </Label>
      <Popover v-model:open="cityOpen">
        <PopoverTrigger as-child>
          <Button variant="outline" role="combobox" class="w-full justify-between"
            :disabled="disabled || !selectedProvinceId" @keydown="handleTriggerKeydown($event, 'city')">
            {{ renderButtonText(selectedCityName, selectedProvinceId ? 'Pilih kota' : 'Pilih provinsi dulu') }}
            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
          </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[280px] p-0">
          <Command ref="cityCommandRef">
            <CommandInput placeholder="Cari kota..." />
            <CommandEmpty>Kota/Kabupaten tidak ditemukan.</CommandEmpty>
            <CommandGroup>
              <CommandItem v-for="city in cities" :key="city.id" :value="city.name" @select="() => setCity(city)">
                <Check class="mr-2 h-4 w-4"
                  :class="isSameId(selectedCityId, city.id) ? 'opacity-100' : 'opacity-0'" />
                {{ city.name }}
              </CommandItem>
            </CommandGroup>
          </Command>
        </PopoverContent>
      </Popover>
      <p v-if="errors?.cityId" class="text-xs text-red-600">
        {{ errors.cityId }}
      </p>
    </div>

    <div v-if="showDistrict" class="space-y-2 sm:col-span-2">
      <Label class="flex items-center gap-1">
        Kecamatan
        <span v-if="props.districtRequired" class="text-red-500">*</span>
      </Label>
      <Popover v-model:open="districtOpen">
        <PopoverTrigger as-child>
          <Button variant="outline" role="combobox" class="w-full justify-between"
            :disabled="disabled || !selectedCityId" @keydown="handleTriggerKeydown($event, 'district')">
            {{ renderButtonText(selectedDistrictName, selectedCityId ? 'Pilih kecamatan' : 'Pilih kota dulu') }}
            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
          </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[320px] p-0">
          <Command ref="districtCommandRef">
            <CommandInput placeholder="Cari kecamatan..." />
            <CommandEmpty>Kecamatan tidak ditemukan.</CommandEmpty>
            <CommandGroup>
              <CommandItem v-for="district in districts" :key="district.id" :value="district.name"
                @select="() => setDistrict(district)">
                <Check class="mr-2 h-4 w-4"
                  :class="isSameId(selectedDistrictId, district.id) ? 'opacity-100' : 'opacity-0'" />
                {{ district.name }}
              </CommandItem>
            </CommandGroup>
          </Command>
        </PopoverContent>
      </Popover>
      <p v-if="errors?.districtId" class="text-xs text-red-600">
        {{ errors.districtId }}
      </p>
    </div>
  </div>
</template>
