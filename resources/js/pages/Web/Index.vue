<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Users, ExternalLink } from '@lucide/vue';
import { computed } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import Breadcrumb from '@/components/UI/Breadcrumb.vue';
import { useTheme } from '@/composables/useTheme';
import { useLocale } from '@/composables/useLocale';

interface Member {
    id: number; name: string; role_label: string; group: string | null;
    bio: string | null; avatar: string | null; color: string | null;
    discord: string | null; steam: string | null; profile_url: string | null;
}
interface Group { group: string | null; members: Member[] }

defineProps<{ groups: Group[]; count: number }>();

const { theme } = useTheme();
const { t } = useLocale();
const dark = computed(() => theme.value === 'dark');

const card = computed(() => dark.value ? 'border-zinc-800/70 bg-[#111113]' : 'border-zinc-200 bg-white');
const textPri = computed(() => dark.value ? 'text-zinc-100' : 'text-zinc-900');
const textMute = computed(() => dark.value ? 'text-zinc-500' : 'text-zinc-400');

function initial(name: string): string { return (name[0] ?? '?').toUpperCase(); }
function avatarBg(name: string): string {
    const colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#10b981', '#f59e0b', '#ef4444'];
    return colors[(name.charCodeAt(0) ?? 0) % colors.length];
}
</script>

<template>
    <Head :title="t('ext.staff.title')" />
    <PublicLayout>
        <div class="border-b" :class="dark ? 'border-zinc-800/60 bg-[#09090b]' : 'border-zinc-200 bg-zinc-50'">
            <div class="relative max-w-[1600px] mx-auto px-4 sm:px-6 py-14">
                <div class="absolute inset-0 pointer-events-none overflow-hidden">
                    <div class="absolute -top-40 left-1/3 w-[600px] h-[400px] rounded-full blur-[130px]"
                        :class="dark ? 'bg-blue-500/6' : 'bg-blue-400/8'" />
                </div>
                <div class="relative z-10">
                    <Breadcrumb :items="[{ label: 'Home', href: '/' }, { label: t('ext.staff.title') }]" />
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-[11px] font-bold uppercase tracking-widest mt-4 mb-5"
                        :class="dark ? 'border-blue-500/25 bg-blue-500/8 text-blue-400' : 'border-blue-400/30 bg-blue-50 text-blue-600'">
                        <Users :size="11" :stroke-width="2.2" /> {{ t('ext.staff.nav') }}
                    </div>
                    <h1 class="text-4xl font-black tracking-tight" :class="textPri">{{ t('ext.staff.title') }}</h1>
                    <p class="mt-3 text-[15px] max-w-lg" :class="textMute">{{ t('ext.staff.subtitle') }}</p>
                </div>
            </div>
        </div>

        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 py-8">
            <div v-if="!count" class="rounded-xl border px-5 py-16 text-center" :class="card">
                <Users :size="26" :stroke-width="1.5" class="mx-auto mb-2" :class="textMute" />
                <p class="text-[13px]" :class="textMute">{{ t('ext.staff.no_members') }}</p>
            </div>

            <div v-for="(g, gi) in groups" :key="gi" class="mb-8 last:mb-0">
                <h2 class="text-[13px] font-black uppercase tracking-widest mb-4" :class="textMute">
                    {{ g.group ?? t('ext.staff.ungrouped') }}
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <div v-for="m in g.members" :key="m.id"
                        class="rounded-2xl border p-5 flex flex-col items-center text-center" :class="card">
                        <div class="w-20 h-20 rounded-2xl overflow-hidden mb-3 ring-2 ring-offset-2"
                            :style="{ '--tw-ring-color': (m.color || '#3b82f6') + '55', '--tw-ring-offset-color': dark ? '#111113' : '#fff' }">
                            <img v-if="m.avatar" :src="m.avatar" :alt="m.name" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center text-2xl font-black text-white"
                                :style="{ backgroundColor: avatarBg(m.name) }">{{ initial(m.name) }}</div>
                        </div>
                        <p class="text-[15px] font-black" :class="textPri">{{ m.name }}</p>
                        <span class="mt-1 inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                            :style="{ backgroundColor: (m.color || '#3b82f6') + '22', color: m.color || '#3b82f6' }">
                            {{ m.role_label }}
                        </span>
                        <p v-if="m.bio" class="mt-3 text-[12px] leading-relaxed" :class="textMute">{{ m.bio }}</p>

                        <div class="flex items-center gap-2 mt-4">
                            <a v-if="m.discord" :href="'https://discord.com/users/' + m.discord" target="_blank" rel="noopener"
                                class="text-[11px] font-semibold px-2.5 py-1 rounded-lg border transition-colors"
                                :class="dark ? 'border-zinc-800 text-zinc-500 hover:text-indigo-400 hover:border-indigo-500/40' : 'border-zinc-200 text-zinc-500 hover:text-indigo-500'">Discord</a>
                            <a v-if="m.steam" :href="m.steam" target="_blank" rel="noopener"
                                class="text-[11px] font-semibold px-2.5 py-1 rounded-lg border transition-colors"
                                :class="dark ? 'border-zinc-800 text-zinc-500 hover:text-blue-400 hover:border-blue-500/40' : 'border-zinc-200 text-zinc-500 hover:text-blue-500'">Steam</a>
                            <Link v-if="m.profile_url" :href="m.profile_url"
                                class="text-[11px] font-semibold px-2.5 py-1 rounded-lg border flex items-center gap-1 transition-colors"
                                :class="dark ? 'border-zinc-800 text-zinc-500 hover:text-zinc-200 hover:border-zinc-700' : 'border-zinc-200 text-zinc-500 hover:text-zinc-800'">
                                <ExternalLink :size="10" :stroke-width="2" /> {{ t('ext.staff.view_profile') }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
