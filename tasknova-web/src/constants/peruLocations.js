// Reserved for the official Peruvian geographical catalogue.
// The profile form consumes this shape without relying on placeholder locations.
export const peruLocations = {
  departments: [],
  provincesByDepartment: {},
  districtsByProvince: {},
}

export function getProvinces(department) {
  return peruLocations.provincesByDepartment[department] || []
}

export function getDistricts(department, province) {
  return peruLocations.districtsByProvince[`${department}:${province}`] || []
}
