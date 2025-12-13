import axios from 'axios';
import { ref } from 'vue';

export type RegionOption = {
  id: number;
  code: string;
  name: string;
};

const normalize = (value?: string | null) => value?.toLowerCase().trim();

export const useIndonesiaRegions = () => {
  const provinces = ref<RegionOption[]>([]);
  const cities = ref<RegionOption[]>([]);
  const districts = ref<RegionOption[]>([]);

  const loadProvinces = async () => {
    const { data } = await axios.get<RegionOption[]>('/regions/provinces');
    provinces.value = data;
  };

  const loadCities = async (provinceCode?: string | null) => {
    if (!provinceCode) {
      cities.value = [];
      return;
    }

    const { data } = await axios.get<RegionOption[]>('/regions/cities', {
      params: { province_code: provinceCode },
    });

    cities.value = data;
  };

  const loadDistricts = async (cityCode?: string | null) => {
    if (!cityCode) {
      districts.value = [];
      return;
    }

    const { data } = await axios.get<RegionOption[]>('/regions/districts', {
      params: { city_code: cityCode },
    });

    districts.value = data;
  };

  const findProvinceByName = (name?: string | null) => {
    const normalized = normalize(name);

    if (!normalized) {
      return null;
    }

    return (
      provinces.value.find((province) => normalize(province.name) === normalized)
      ?? provinces.value.find((province) => normalize(province.name)?.includes(normalized ?? '') ?? false)
      ?? null
    );
  };

  const findCityByName = (name?: string | null) => {
    const normalized = normalize(name);

    if (!normalized) {
      return null;
    }

    return (
      cities.value.find((city) => normalize(city.name) === normalized)
      ?? cities.value.find((city) => normalize(city.name)?.includes(normalized ?? '') ?? false)
      ?? null
    );
  };

  const findDistrictByName = (name?: string | null) => {
    const normalized = normalize(name);

    if (!normalized) {
      return null;
    }

    return (
      districts.value.find((district) => normalize(district.name) === normalized)
      ?? districts.value.find((district) => normalize(district.name)?.includes(normalized ?? '') ?? false)
      ?? null
    );
  };

  return {
    provinces,
    cities,
    districts,
    loadProvinces,
    loadCities,
    loadDistricts,
    findProvinceByName,
    findCityByName,
    findDistrictByName,
  };
};
