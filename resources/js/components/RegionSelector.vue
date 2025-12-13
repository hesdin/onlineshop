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
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps<{
  provinceId: number | null;
  cityId: number | null;
  districtId?: number | null;
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

const selectedProvinceId = ref<number | null>(null);
const selectedCityId = ref<number | null>(null);
const selectedDistrictId = ref<number | null>(null);

const selectedProvinceName = computed(() => {
  if (selectedProvinceId.value) {
    const province = provinces.value.find((province) => province.id === selectedProvinceId.value);
    if (province) {
      return province.name;
    }
  }

  return props.initialProvinceName ?? '';
});

const selectedCityName = computed(() => {
  if (selectedCityId.value) {
    const city = cities.value.find((city) => city.id === selectedCityId.value);
    if (city) {
      return city.name;
    }
  }

  return props.initialCityName ?? '';
});

const selectedDistrictName = computed(() => {
  if (selectedDistrictId.value) {
    const district = districts.value.find((district) => district.id === selectedDistrictId.value);
    if (district) {
      return district.name;
    }
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

watch(
  () => props.provinceId,
  async (value) => {
    await ensureProvincesLoaded();

    if (!value) {
      if (selectedProvinceId.value) {
        selectedProvinceId.value = null;
        resetCityAndDistrict();
      }
      return;
    }

    if (value !== selectedProvinceId.value) {
      selectedProvinceId.value = value;
      const province = provinces.value.find((item) => item.id === value);
      await loadCities(province?.code);
    }
  },
  { immediate: true },
);

watch(
  () => props.cityId,
  async (value) => {
    if (!value) {
      if (selectedCityId.value) {
        selectedCityId.value = null;
        resetDistrict();
      }
      return;
    }

    await ensureProvincesLoaded();

    const provinceId = selectedProvinceId.value ?? props.provinceId ?? null;
    const province = provinceId ? provinces.value.find((item) => item.id === provinceId) : null;

    if (
      province
      && (!cities.value.length || !cities.value.some((city) => city.id === value))
    ) {
      if (props.citiesProp?.length) {
        cities.value = props.citiesProp.filter((c) => c.province_code === province.code);
      } else {
        await loadCities(province.code);
      }
    }

    if (value !== selectedCityId.value) {
      selectedCityId.value = value;
      const city = cities.value.find((item) => item.id === value);
      if (props.showDistrict) {
        if (
          city
          && (!districts.value.length || !districts.value.some((district) => district.city_code === city.code))
        ) {
          if (props.districtsProp?.length) {
            districts.value = props.districtsProp.filter((d) => d.city_code === city.code);
          } else {
            await loadDistricts(city.code);
          }
        } else if (city) {
          await loadDistricts(city.code);
        }
      }
    }
  },
  { immediate: true },
);

watch(
  () => props.districtId,
  async (value) => {
    if (!props.showDistrict) {
      return;
    }

    if (!value) {
      if (selectedDistrictId.value) {
        selectedDistrictId.value = null;
      }
      return;
    }

    await ensureProvincesLoaded();

    const cityId = selectedCityId.value ?? props.cityId ?? null;

    if (cityId && !cities.value.some((city) => city.id === cityId)) {
      const provinceId = selectedProvinceId.value ?? props.provinceId ?? null;
      const province = provinceId ? provinces.value.find((item) => item.id === provinceId) : null;
      if (province) {
        if (props.citiesProp?.length) {
          cities.value = props.citiesProp.filter((c) => c.province_code === province.code);
        } else {
          await loadCities(province.code);
        }
      }
    }

    const city = cities.value.find((item) => item.id === cityId);

    if (city && (!districts.value.length || !districts.value.some((district) => district.city_code === city.code))) {
      if (props.districtsProp?.length) {
        districts.value = props.districtsProp.filter((d) => d.city_code === city.code);
      } else {
        await loadDistricts(city.code);
      }
    }

    if (value !== selectedDistrictId.value) {
      selectedDistrictId.value = value;
    }
  },
  { immediate: true },
);

watch(
  selectedProvinceId,
  async (id, previous) => {
    if (props.disabled || id === previous) {
      return;
    }

    // If the selected ID matches the prop, it means we are syncing from props (initialization or external update).
    // in this case, we should NOT reset children or emit update, but we might need to ensure cities are loaded.
    if (id === props.provinceId) {
       const province = provinces.value.find((item) => item.id === id);
       if (province?.code) {
           if (!cities.value.length || (props.citiesProp && !cities.value.length)) {
                if (props.citiesProp?.length) {
                    cities.value = props.citiesProp.filter((c) => c.province_code === province.code);
                } else {
                    await loadCities(province.code);
                }
           }
       }
       return;
    }

    const province = provinces.value.find((item) => item.id === id);
    emit('update:provinceId', province?.id ?? null);
    resetCityAndDistrict();

    if (province?.code) {
      if (props.citiesProp?.length) {
        cities.value = props.citiesProp.filter((c) => c.province_code === province.code);
      } else {
        await loadCities(province.code);
      }
    }
  },
);

watch(
  selectedCityId,
  async (id, previous) => {
    if (props.disabled || id === previous) {
      return;
    }

    if (id === props.cityId) {
        const city = cities.value.find((item) => item.id === id);
        if (props.showDistrict && city?.code) {
             if (!districts.value.length) {
                if (props.districtsProp?.length) {
                    districts.value = props.districtsProp.filter((d) => d.city_code === city.code);
                } else {
                    await loadDistricts(city.code);
                }
             }
        }
        return;
    }

    const city = cities.value.find((item) => item.id === id);
    emit('update:cityId', city?.id ?? null);
    resetDistrict();

    if (props.showDistrict && city?.code) {
      if (props.districtsProp?.length) {
        districts.value = props.districtsProp.filter((d) => d.city_code === city.code);
      } else {
        await loadDistricts(city.code);
      }
    }
  },
);

watch(selectedDistrictId, (id, previous) => {
  if (!props.showDistrict || props.disabled || id === previous) {
    return;
  }

  if (id === props.districtId) {
      return;
  }

  const district = districts.value.find((item) => item.id === id);
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
    const province = provinces.value.find((item) => item.id === props.provinceId);
    if (province) {
      selectedProvinceId.value = province.id;
      await loadCities(province.code);
    }
  }

  if (props.cityId && selectedProvinceId.value) {
    const city = cities.value.find((item) => item.id === props.cityId);
    if (city) {
      selectedCityId.value = city.id;
      if (props.showDistrict) {
        await loadDistricts(city.code);
      }
    }
  }

  if (props.showDistrict && props.districtId && selectedCityId.value) {
    const district = districts.value.find((item) => item.id === props.districtId);
    if (district) {
      selectedDistrictId.value = district.id;
    }
  }
});

const renderButtonText = (text: string, fallback: string) => text || fallback;
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
          <Button variant="outline" role="combobox" class="w-full justify-between" :disabled="disabled">
            {{ renderButtonText(selectedProvinceName, 'Pilih provinsi') }}
            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
          </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[280px] p-0">
          <Command>
            <CommandInput placeholder="Cari provinsi..." />
            <CommandEmpty>Provinsi tidak ditemukan.</CommandEmpty>
            <CommandGroup>
              <CommandItem v-for="province in provinces" :key="province.id" :value="province.name"
                @select="() => { selectedProvinceId = province.id; provinceOpen = false; }">
                <Check class="mr-2 h-4 w-4"
                  :class="selectedProvinceId === province.id ? 'opacity-100' : 'opacity-0'" />
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
            :disabled="disabled || !selectedProvinceId">
            {{ renderButtonText(selectedCityName, selectedProvinceId ? 'Pilih kota' : 'Pilih provinsi dulu') }}
            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
          </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[280px] p-0">
          <Command>
            <CommandInput placeholder="Cari kota..." />
            <CommandEmpty>Kota/Kabupaten tidak ditemukan.</CommandEmpty>
            <CommandGroup>
              <CommandItem v-for="city in cities" :key="city.id" :value="city.name"
                @select="() => { selectedCityId = city.id; cityOpen = false; }">
                <Check class="mr-2 h-4 w-4" :class="selectedCityId === city.id ? 'opacity-100' : 'opacity-0'" />
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
            :disabled="disabled || !selectedCityId">
            {{ renderButtonText(selectedDistrictName, selectedCityId ? 'Pilih kecamatan' : 'Pilih kota dulu') }}
            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
          </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[320px] p-0">
          <Command>
            <CommandInput placeholder="Cari kecamatan..." />
            <CommandEmpty>Kecamatan tidak ditemukan.</CommandEmpty>
            <CommandGroup>
              <CommandItem v-for="district in districts" :key="district.id" :value="district.name"
                @select="() => { selectedDistrictId = district.id; districtOpen = false; }">
                <Check class="mr-2 h-4 w-4"
                  :class="selectedDistrictId === district.id ? 'opacity-100' : 'opacity-0'" />
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
