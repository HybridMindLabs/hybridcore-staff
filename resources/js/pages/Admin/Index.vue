<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { Users, Plus, Pencil, Trash2, X, Search, Check } from '@lucide/vue';
import { ref, watch } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PageHeader from '@/components/UI/PageHeader.vue';
import { useLocale } from '@/composables/useLocale';

interface Member {
    id: number; user_id: number | null; name: string; role_label: string; group: string | null;
    bio: string | null; avatar: string | null; color: string | null;
    discord: string | null; steam: string | null; is_active: boolean; sort: number; linked_username: string | null;
}

defineProps<{ members: Member[] }>();
const { t } = useLocale();

const editing = ref<Member | null>(null);
const showForm = ref(false);

const form = useForm({
    user_id: null as number | null, name: '', role_label: '', group: '', bio: '',
    avatar: '', color: '#3b82f6', discord: '', steam: '', is_active: true, sort: 0,
});

// ── Linked-user autocomplete ─────────────────────────────────
interface UserHit { id: number; username: string; display_name: string; avatar: string | null }
const linkedUser = ref<UserHit | null>(null);
const userQuery = ref('');
const userResults = ref<UserHit[]>([]);
const userOpen = ref(false);
let userDebounce: ReturnType<typeof setTimeout> | null = null;

watch(userQuery, (val) => {
    if (userDebounce) clearTimeout(userDebounce);
    userDebounce = setTimeout(async () => {
        try {
            const r = await fetch(route('admin.staff.users') + '?q=' + encodeURIComponent(val), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const json = await r.json();
            userResults.value = json.users ?? [];
            userOpen.value = true;
        } catch {
            userResults.value = [];
        }
    }, 200);
});

function pickUser(u: UserHit) {
    linkedUser.value = u;
    form.user_id = u.id;
    if (!form.name) form.name = u.display_name;
    userOpen.value = false;
    userQuery.value = '';
}
function unlinkUser() {
    linkedUser.value = null;
    form.user_id = null;
}
function focusUserSearch() {
    if (!userResults.value.length) { userQuery.value = ''; }
    userOpen.value = true;
}

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    linkedUser.value = null;
    userQuery.value = '';
    userOpen.value = false;
    showForm.value = true;
}
function openEdit(m: Member) {
    editing.value = m;
    form.clearErrors();
    form.user_id = m.user_id;
    linkedUser.value = m.user_id ? { id: m.user_id, username: m.linked_username ?? '', display_name: m.linked_username ?? '', avatar: m.avatar } : null;
    userQuery.value = '';
    userOpen.value = false;
    form.name = m.name;
    form.role_label = m.role_label;
    form.group = m.group ?? '';
    form.bio = m.bio ?? '';
    form.avatar = m.avatar ?? '';
    form.color = m.color ?? '#3b82f6';
    form.discord = m.discord ?? '';
    form.steam = m.steam ?? '';
    form.is_active = m.is_active;
    form.sort = m.sort;
    showForm.value = true;
}
function submit() {
    if (editing.value) {
        form.put(route('admin.staff.update', editing.value.id), { onSuccess: () => (showForm.value = false) });
    } else {
        form.post(route('admin.staff.store'), { onSuccess: () => (showForm.value = false) });
    }
}
function destroy(m: Member) {
    if (!confirm(t('ext.staff.confirm_delete').replace(':name', m.name))) return;
    router.delete(route('admin.staff.destroy', m.id), { preserveScroll: true });
}

const input = 'w-full bg-zinc-900/60 border border-zinc-800/70 text-zinc-100 rounded-lg px-3 py-2 text-sm placeholder:text-zinc-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20';
const label = 'text-zinc-400 text-[11px] font-bold uppercase tracking-wider mb-1.5 block';
</script>

<template>
    <Head :title="t('ext.staff.admin_title')" />
    <AdminLayout :title="t('ext.staff.admin_title')">
        <div class="flex items-start justify-between gap-4 mb-5">
            <PageHeader :title="t('ext.staff.admin_title')" :description="t('ext.staff.admin_subtitle')" :icon="Users" />
            <button type="button" class="shrink-0 flex items-center gap-1.5 bg-blue-500 text-white font-semibold rounded-lg px-3 py-2 text-xs hover:bg-blue-400 transition-colors" @click="openCreate">
                <Plus :size="13" :stroke-width="2" /> {{ t('ext.staff.add_member') }}
            </button>
        </div>

        <div class="bg-[#111113] border border-zinc-800/70 rounded-xl overflow-hidden">
            <div v-if="!members.length" class="px-5 py-12 text-center">
                <Users :size="24" :stroke-width="1.5" class="mx-auto text-zinc-700 mb-2" />
                <p class="text-[13px] text-zinc-600">{{ t('ext.staff.no_members_admin') }}</p>
            </div>

            <div v-for="m in members" :key="m.id" class="px-5 py-3.5 border-b border-zinc-800/50 last:border-0 flex items-center gap-4">
                <div class="w-9 h-9 rounded-lg shrink-0 overflow-hidden border border-zinc-800 bg-zinc-900 flex items-center justify-center">
                    <img v-if="m.avatar" :src="m.avatar" class="w-full h-full object-cover" />
                    <Users v-else :size="15" :stroke-width="1.8" class="text-zinc-600" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-[14px] font-bold text-zinc-100 truncate">{{ m.name }}</p>
                        <span v-if="!m.is_active" class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-zinc-800 text-zinc-500">off</span>
                        <span v-if="m.linked_username" class="text-[10px] font-mono text-zinc-600">@{{ m.linked_username }}</span>
                    </div>
                    <p class="text-[11px] text-zinc-500">
                        <span :style="{ color: m.color || '#3b82f6' }">{{ m.role_label }}</span>
                        <span v-if="m.group" class="text-zinc-700"> · {{ m.group }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" :title="t('ext.staff.edit_member')" class="w-7 h-7 flex items-center justify-center rounded-lg text-zinc-500 hover:text-zinc-100 hover:bg-zinc-800 transition-colors" @click="openEdit(m)">
                        <Pencil :size="13" :stroke-width="1.75" />
                    </button>
                    <button type="button" class="w-7 h-7 flex items-center justify-center rounded-lg text-zinc-500 hover:text-red-400 hover:bg-red-500/10 transition-colors" @click="destroy(m)">
                        <Trash2 :size="13" :stroke-width="1.75" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Create / edit modal -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 py-8 overflow-y-auto" @mousedown.self="showForm = false">
            <div class="w-full max-w-lg bg-[#111113] border border-zinc-800 rounded-2xl p-6 my-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-zinc-100 text-base font-bold">{{ editing ? t('ext.staff.edit_member') : t('ext.staff.add_member') }}</h3>
                    <button type="button" class="text-zinc-600 hover:text-zinc-300" @click="showForm = false"><X :size="16" /></button>
                </div>
                <form class="flex flex-col gap-3" @submit.prevent="submit">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label :class="label">{{ t('ext.staff.field_name') }}</label>
                            <input v-model="form.name" type="text" :class="input" />
                            <p v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label :class="label">{{ t('ext.staff.field_role') }}</label>
                            <input v-model="form.role_label" type="text" :class="input" placeholder="Founder" />
                            <p v-if="form.errors.role_label" class="text-red-400 text-xs mt-1">{{ form.errors.role_label }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label :class="label">{{ t('ext.staff.field_group') }}</label>
                            <input v-model="form.group" type="text" :class="input" placeholder="Management" />
                        </div>
                        <div class="relative">
                            <label :class="label">{{ t('ext.staff.field_user') }}</label>

                            <!-- Selected user chip -->
                            <div v-if="linkedUser" class="flex items-center gap-2 bg-zinc-900/60 border border-zinc-800/70 rounded-lg px-2.5 py-2">
                                <span class="w-6 h-6 rounded-md overflow-hidden shrink-0 bg-zinc-800 flex items-center justify-center">
                                    <img v-if="linkedUser.avatar" :src="linkedUser.avatar" class="w-full h-full object-cover" />
                                    <span v-else class="text-[10px] font-bold text-zinc-500 uppercase">{{ (linkedUser.username[0] ?? '?') }}</span>
                                </span>
                                <span class="flex-1 text-[13px] text-zinc-200 font-medium truncate">@{{ linkedUser.username }}</span>
                                <button type="button" class="text-zinc-600 hover:text-red-400" @click="unlinkUser"><X :size="14" /></button>
                            </div>

                            <!-- Search input -->
                            <div v-else>
                                <div class="flex items-center gap-2 bg-zinc-900/60 border border-zinc-800/70 rounded-lg px-2.5 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500/20">
                                    <Search :size="13" :stroke-width="2" class="text-zinc-600 shrink-0" />
                                    <input v-model="userQuery" type="text" class="flex-1 bg-transparent py-2 text-sm text-zinc-100 placeholder:text-zinc-600 focus:outline-none"
                                        :placeholder="t('ext.staff.search_user')" @focus="focusUserSearch" />
                                </div>
                                <div v-if="userOpen && userResults.length" class="absolute z-10 left-0 right-0 mt-1 bg-[#18181b] border border-zinc-800 rounded-lg shadow-xl py-1 max-h-52 overflow-y-auto">
                                    <button v-for="u in userResults" :key="u.id" type="button"
                                        class="flex items-center gap-2.5 w-full px-3 py-2 text-left hover:bg-zinc-800 transition-colors"
                                        @click="pickUser(u)">
                                        <span class="w-6 h-6 rounded-md overflow-hidden shrink-0 bg-zinc-800 flex items-center justify-center">
                                            <img v-if="u.avatar" :src="u.avatar" class="w-full h-full object-cover" />
                                            <span v-else class="text-[10px] font-bold text-zinc-500 uppercase">{{ (u.username[0] ?? '?') }}</span>
                                        </span>
                                        <span class="flex-1 min-w-0">
                                            <span class="block text-[13px] text-zinc-200 font-medium truncate">{{ u.display_name }}</span>
                                            <span class="block text-[11px] text-zinc-600 font-mono truncate">@{{ u.username }}</span>
                                        </span>
                                        <Check v-if="form.user_id === u.id" :size="13" class="text-blue-400 shrink-0" />
                                    </button>
                                </div>
                                <div v-else-if="userOpen && userQuery.length >= 1 && !userResults.length" class="absolute z-10 left-0 right-0 mt-1 bg-[#18181b] border border-zinc-800 rounded-lg px-3 py-2.5 text-[12px] text-zinc-600">
                                    {{ t('ext.staff.no_users') }}
                                </div>
                            </div>
                            <p class="text-zinc-600 text-[10px] mt-1">{{ t('ext.staff.field_user_hint') }}</p>
                        </div>
                    </div>
                    <div>
                        <label :class="label">{{ t('ext.staff.field_bio') }}</label>
                        <textarea v-model="form.bio" rows="2" :class="input" />
                    </div>
                    <div>
                        <label :class="label">{{ t('ext.staff.field_avatar') }}</label>
                        <input v-model="form.avatar" type="url" :class="input" placeholder="https://.../avatar.png" />
                        <p class="text-zinc-600 text-[10px] mt-1">{{ t('ext.staff.field_avatar_hint') }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label :class="label">{{ t('ext.staff.field_color') }}</label>
                            <input v-model="form.color" type="text" :class="[input, 'font-mono']" placeholder="#3b82f6" />
                        </div>
                        <div>
                            <label :class="label">{{ t('ext.staff.field_discord') }}</label>
                            <input v-model="form.discord" type="text" :class="input" />
                        </div>
                        <div>
                            <label :class="label">{{ t('ext.staff.field_sort') }}</label>
                            <input v-model.number="form.sort" type="number" min="0" :class="input" />
                        </div>
                    </div>
                    <div>
                        <label :class="label">{{ t('ext.staff.field_steam') }}</label>
                        <input v-model="form.steam" type="url" :class="input" placeholder="https://steamcommunity.com/id/..." />
                    </div>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 accent-blue-500" />
                        <span class="text-zinc-300 text-sm">{{ t('ext.staff.field_active') }}</span>
                    </label>
                    <button type="submit" :disabled="form.processing" class="mt-2 bg-blue-500 text-white font-semibold rounded-lg px-4 py-2 text-sm hover:bg-blue-400 transition-colors disabled:opacity-50">
                        {{ editing ? t('ext.staff.edit_member') : t('ext.staff.add_member') }}
                    </button>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
