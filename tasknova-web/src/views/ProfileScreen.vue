<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { AlertCircle, BookOpen, LoaderCircle, LogOut, MapPin, Save, ShieldCheck, UserRound, X } from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import BottomNav from '../components/navigation/BottomNav.vue'
import ProfileHeader from '../components/profile/ProfileHeader.vue'
import { useAuth } from '../composables/useAuth'
import { useProfileStore } from '../stores/profile'

const locationModules = import.meta.glob('../constants/*Locations.js', { eager: true })
const peruCatalog = locationModules['../constants/peruLocations.js']?.peruLocations
const punoCatalog = locationModules['../constants/punoLocations.js']?.punoLocations
const locations = Array.isArray(peruCatalog) ? peruCatalog : punoCatalog ?? []

const router = useRouter()
const { user, logout } = useAuth()
const profileStore = useProfileStore()
const { profile, isLoading, isSaving, error, successMessage } = storeToRefs(profileStore)

const avatarFile = ref(null)
const avatarPreview = ref('')
const avatarError = ref('')
const initialForm = ref(null)
const touched = reactive({})

function initialValues(source = {}) {
  const nameParts = (source.name || user.value?.name || '').trim().split(/\s+/).filter(Boolean)
  return {
    first_name: source.first_name ?? nameParts[0] ?? '',
    last_name: source.last_name ?? nameParts.slice(1).join(' ') ?? '',
    email: source.email ?? user.value?.email ?? '',
    phone: source.phone ?? '',
    birth_date: source.birth_date ?? '',
    sex: source.sex ?? '',
    institution: source.institution ?? '',
    education_level: source.education_level ?? '',
    career: source.career ?? '',
    grade: source.grade ?? '',
    cycle: source.cycle ?? '',
    department: source.department ?? '',
    province: source.province ?? '',
    district: source.district ?? '',
    avatar_url: source.avatar_url ?? '',
  }
}

const form = reactive(initialValues())

function assignForm(source = {}) {
  Object.assign(form, initialValues(source))
  avatarPreview.value = form.avatar_url
  initialForm.value = { ...form }
}

const departments = computed(() => locations)
const selectedDepartment = computed(() => departments.value.find((department) => department.name === form.department))
const provinces = computed(() => selectedDepartment.value?.provinces ?? [])
const districts = computed(() => provinces.value.find((province) => province.name === form.province)?.districts ?? [])

const validation = computed(() => ({
  first_name: !form.first_name.trim() ? 'Ingresa tus nombres.' : form.first_name.length > 100 ? 'Máximo 100 caracteres.' : '',
  last_name: !form.last_name.trim() ? 'Ingresa tus apellidos.' : form.last_name.length > 100 ? 'Máximo 100 caracteres.' : '',
  phone: !form.phone ? 'Ingresa tu teléfono.' : !/^(?:\+51\s?)?9\d{8}$/.test(form.phone.replace(/[()-]/g, '')) ? 'Usa un celular peruano válido.' : '',
  birth_date: !form.birth_date ? 'Selecciona tu fecha de nacimiento.' : '',
  sex: !form.sex ? 'Selecciona una opción.' : '',
  institution: !form.institution.trim() ? 'Ingresa tu institución.' : form.institution.length > 160 ? 'Máximo 160 caracteres.' : '',
  education_level: !form.education_level ? 'Selecciona tu nivel educativo.' : '',
  career: !form.career.trim() ? 'Ingresa tu carrera o especialidad.' : form.career.length > 160 ? 'Máximo 160 caracteres.' : '',
  grade: !form.grade.trim() ? 'Ingresa tu grado.' : form.grade.length > 80 ? 'Máximo 80 caracteres.' : '',
  cycle: !form.cycle.trim() ? 'Ingresa tu ciclo.' : form.cycle.length > 40 ? 'Máximo 40 caracteres.' : '',
  department: !form.department ? 'Selecciona tu departamento.' : '',
  province: !form.province ? 'Selecciona tu provincia.' : '',
  district: !form.district ? 'Selecciona tu distrito.' : '',
}))

function fieldError(field) {
  return touched[field] ? validation.value[field] : ''
}

const formValid = computed(() => Object.values(validation.value).every((message) => !message))
const fullName = computed(() => `${form.first_name} ${form.last_name}`.trim())

function touch(field) {
  touched[field] = true
}

function onDepartmentChange() {
  form.province = ''
  form.district = ''
  touch('department')
}

function onProvinceChange() {
  form.district = ''
  touch('province')
}

function selectAvatar(file) {
  avatarError.value = ''
  if (!file) return
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    avatarError.value = 'Selecciona una imagen JPG, PNG o WEBP.'
    return
  }
  if (file.size > 5 * 1024 * 1024) {
    avatarError.value = 'La imagen no debe superar los 5 MB.'
    return
  }
  avatarFile.value = file
  avatarPreview.value = URL.createObjectURL(file)
}

function removeAvatar() {
  avatarFile.value = null
  avatarPreview.value = ''
  form.avatar_url = ''
}

function cancelChanges() {
  if (initialForm.value) Object.assign(form, initialForm.value)
  avatarFile.value = null
  avatarPreview.value = form.avatar_url
  avatarError.value = ''
  Object.keys(touched).forEach((key) => delete touched[key])
  profileStore.clearMessages()
}

async function saveProfile() {
  Object.keys(validation.value).forEach((field) => touch(field))
  if (!formValid.value || avatarError.value) return

  const saved = await profileStore.updateProfile({ ...form, avatar_url: undefined }, avatarFile.value)
  if (saved) assignForm(saved)
}

async function handleLogout() {
  await logout()
  router.replace('/login')
}

onMounted(async () => {
  profileStore.clearMessages()
  const loaded = await profileStore.fetchProfile()
  if (loaded) assignForm(loaded)
  else assignForm({ email: user.value?.email, name: user.value?.name })
})
</script>

<template>
  <div class="tn-page min-h-screen pb-28">
    <header class="tn-header sticky top-0 z-10 border-b backdrop-blur-md">
      <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.14em] text-primary-700">Cuenta</p>
          <h2 class="font-display text-xl font-bold text-brand-ink">Mi Perfil</h2>
        </div>
        <span class="rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">Datos personales</span>
      </div>
    </header>

    <main class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6">
      <ProfileHeader :name="fullName" :image-url="avatarPreview" @select-image="selectAvatar" @remove-image="removeAvatar" />
      <p v-if="avatarError" class="-mt-3 text-center text-sm text-red-600 sm:text-left">{{ avatarError }}</p>

      <div v-if="isLoading" class="tn-card flex items-center justify-center gap-3 rounded-2xl bg-white/90 p-8 text-brand-muted">
        <LoaderCircle class="h-5 w-5 animate-spin text-primary-600" aria-hidden="true" />
        Cargando información de perfil…
      </div>

      <form v-else class="space-y-6" novalidate @submit.prevent="saveProfile">
        <div v-if="error" role="alert" class="flex gap-3 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">
          <AlertCircle class="mt-0.5 h-5 w-5 shrink-0" aria-hidden="true" />
          <span>{{ error }}</span>
        </div>
        <div v-if="successMessage" role="status" class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
          {{ successMessage }}
        </div>

        <section class="tn-card rounded-3xl bg-white/90 p-5 sm:p-6">
          <div class="mb-6 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-700"><UserRound class="h-5 w-5" aria-hidden="true" /></span>
            <div><h3 class="font-display text-lg font-bold text-brand-ink">Información Personal</h3><p class="text-sm text-brand-muted">Datos para identificarte y mantenernos en contacto.</p></div>
          </div>
          <div class="grid gap-5 sm:grid-cols-2">
            <div><label for="first-name" class="tn-label">Nombres</label><input id="first-name" v-model="form.first_name" class="tn-input" :class="{ 'border-red-400': fieldError('first_name') }" maxlength="100" @input="touch('first_name')" @blur="touch('first_name')" /><p v-if="fieldError('first_name')" class="tn-field-error">{{ fieldError('first_name') }}</p></div>
            <div><label for="last-name" class="tn-label">Apellidos</label><input id="last-name" v-model="form.last_name" class="tn-input" :class="{ 'border-red-400': fieldError('last_name') }" maxlength="100" @input="touch('last_name')" @blur="touch('last_name')" /><p v-if="fieldError('last_name')" class="tn-field-error">{{ fieldError('last_name') }}</p></div>
            <div><label for="profile-email" class="tn-label">Correo electrónico</label><input id="profile-email" :value="form.email" type="email" readonly class="tn-input cursor-not-allowed bg-slate-100 text-slate-500" /><p class="mt-1.5 text-xs text-brand-muted">El correo se administra desde tu cuenta.</p></div>
            <div><label for="phone" class="tn-label">Teléfono</label><input id="phone" v-model="form.phone" type="tel" inputmode="tel" placeholder="987654321" class="tn-input" :class="{ 'border-red-400': fieldError('phone') }" maxlength="16" @input="touch('phone')" @blur="touch('phone')" /><p v-if="fieldError('phone')" class="tn-field-error">{{ fieldError('phone') }}</p></div>
            <div><label for="birth-date" class="tn-label">Fecha de nacimiento</label><input id="birth-date" v-model="form.birth_date" type="date" class="tn-input" :class="{ 'border-red-400': fieldError('birth_date') }" @input="touch('birth_date')" @blur="touch('birth_date')" /><p v-if="fieldError('birth_date')" class="tn-field-error">{{ fieldError('birth_date') }}</p></div>
            <div><label for="sex" class="tn-label">Sexo</label><select id="sex" v-model="form.sex" class="tn-input" :class="{ 'border-red-400': fieldError('sex') }" @change="touch('sex')"><option value="">Selecciona una opción</option><option value="male">Masculino</option><option value="female">Femenino</option><option value="undisclosed">Prefiero no indicar</option></select><p v-if="fieldError('sex')" class="tn-field-error">{{ fieldError('sex') }}</p></div>
          </div>
        </section>

        <section class="tn-card rounded-3xl bg-white/90 p-5 sm:p-6">
          <div class="mb-6 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-secondary-50 text-secondary-600"><BookOpen class="h-5 w-5" aria-hidden="true" /></span><div><h3 class="font-display text-lg font-bold text-brand-ink">Información Académica</h3><p class="text-sm text-brand-muted">Cuéntanos sobre tu formación actual.</p></div></div>
          <div class="grid gap-5 sm:grid-cols-2">
            <div><label for="institution" class="tn-label">Institución educativa</label><input id="institution" v-model="form.institution" class="tn-input" :class="{ 'border-red-400': fieldError('institution') }" maxlength="160" @input="touch('institution')" @blur="touch('institution')" /><p v-if="fieldError('institution')" class="tn-field-error">{{ fieldError('institution') }}</p></div>
            <div><label for="education-level" class="tn-label">Nivel educativo</label><select id="education-level" v-model="form.education_level" class="tn-input" :class="{ 'border-red-400': fieldError('education_level') }" @change="touch('education_level')"><option value="">Selecciona un nivel</option><option value="primary">Primaria</option><option value="secondary">Secundaria</option><option value="institute">Instituto</option><option value="university">Universidad</option><option value="postgraduate">Posgrado</option><option value="other">Otro</option></select><p v-if="fieldError('education_level')" class="tn-field-error">{{ fieldError('education_level') }}</p></div>
            <div><label for="career" class="tn-label">Carrera o especialidad</label><input id="career" v-model="form.career" class="tn-input" :class="{ 'border-red-400': fieldError('career') }" maxlength="160" @input="touch('career')" @blur="touch('career')" /><p v-if="fieldError('career')" class="tn-field-error">{{ fieldError('career') }}</p></div>
            <div><label for="grade" class="tn-label">Grado</label><input id="grade" v-model="form.grade" class="tn-input" :class="{ 'border-red-400': fieldError('grade') }" maxlength="80" @input="touch('grade')" @blur="touch('grade')" /><p v-if="fieldError('grade')" class="tn-field-error">{{ fieldError('grade') }}</p></div>
            <div><label for="cycle" class="tn-label">Ciclo</label><input id="cycle" v-model="form.cycle" class="tn-input" :class="{ 'border-red-400': fieldError('cycle') }" maxlength="40" @input="touch('cycle')" @blur="touch('cycle')" /><p v-if="fieldError('cycle')" class="tn-field-error">{{ fieldError('cycle') }}</p></div>
          </div>
        </section>

        <section class="tn-card rounded-3xl bg-white/90 p-5 sm:p-6">
          <div class="mb-6 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><MapPin class="h-5 w-5" aria-hidden="true" /></span><div><h3 class="font-display text-lg font-bold text-brand-ink">Procedencia</h3><p class="text-sm text-brand-muted">Catálogos preparados para la división político-administrativa del Perú.</p></div></div>
          <div class="grid gap-5 sm:grid-cols-3">
            <div><label for="department" class="tn-label">Departamento</label><select id="department" v-model="form.department" class="tn-input" :class="{ 'border-red-400': fieldError('department') }" @change="onDepartmentChange"><option value="">Selecciona un departamento</option><option v-for="department in departments" :key="department.id" :value="department.name">{{ department.name }}</option></select><p v-if="fieldError('department')" class="tn-field-error">{{ fieldError('department') }}</p></div>
            <div><label for="province" class="tn-label">Provincia</label><select id="province" v-model="form.province" class="tn-input" :disabled="!form.department || !provinces.length" :class="{ 'border-red-400': fieldError('province') }" @change="onProvinceChange"><option value="">{{ provinces.length ? 'Selecciona una provincia' : 'Catálogo pendiente' }}</option><option v-for="province in provinces" :key="province.id" :value="province.name">{{ province.name }}</option></select><p v-if="fieldError('province')" class="tn-field-error">{{ fieldError('province') }}</p></div>
            <div><label for="district" class="tn-label">Distrito</label><select id="district" v-model="form.district" class="tn-input" :disabled="!form.province || !districts.length" :class="{ 'border-red-400': fieldError('district') }" @change="touch('district')"><option value="">{{ districts.length ? 'Selecciona un distrito' : 'Catálogo pendiente' }}</option><option v-for="district in districts" :key="district.id" :value="district.name">{{ district.name }}</option></select><p v-if="fieldError('district')" class="tn-field-error">{{ fieldError('district') }}</p></div>
          </div>
          <p class="mt-4 text-xs leading-5 text-brand-muted">Catálogo oficial UBIGEO de Puno.</p>
        </section>

        <section class="tn-card rounded-3xl bg-white/90 p-5 sm:p-6">
          <div class="flex items-start gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600"><ShieldCheck class="h-5 w-5" aria-hidden="true" /></span><div><h3 class="font-display text-lg font-bold text-brand-ink">Seguridad</h3><p class="mt-1 text-sm leading-6 text-brand-muted">Próximamente podrás administrar tu contraseña, sesiones activas y métodos de recuperación desde aquí.</p></div></div>
          <button type="button" class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100" @click="handleLogout"><LogOut class="h-4 w-4" aria-hidden="true" />Cerrar sesión</button>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
          <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-brand-muted transition hover:bg-slate-50" @click="cancelChanges"><X class="h-4 w-4" aria-hidden="true" />Cancelar</button>
          <button type="submit" :disabled="isSaving" class="tn-button-primary sm:w-auto sm:px-6"><span class="relative z-10 flex items-center justify-center gap-2"><LoaderCircle v-if="isSaving" class="h-5 w-5 animate-spin" aria-hidden="true" /><Save v-else class="h-5 w-5" aria-hidden="true" />{{ isSaving ? 'Guardando cambios…' : 'Guardar cambios' }}</span></button>
        </div>
      </form>
    </main>

    <BottomNav />
  </div>
</template>
