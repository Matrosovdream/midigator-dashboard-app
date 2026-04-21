<script setup lang="ts">
import api from '@/lib/api';
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Select from 'primevue/select';
import Tab from 'primevue/tab';
import TabList from 'primevue/tablist';
import TabPanel from 'primevue/tabpanel';
import TabPanels from 'primevue/tabpanels';
import Tabs from 'primevue/tabs';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { useConfirm } from 'primevue/useconfirm';
import { computed, onMounted, reactive, ref } from 'vue';

interface Setting {
    id?: number;
    key: string;
    value: any;
    type: 'string' | 'integer' | 'boolean' | 'json';
    group: string;
    description?: string | null;
}

interface SettingGroup {
    name: string;
    settings: Setting[];
}

const confirm = useConfirm();

const groups = ref<SettingGroup[]>([]);
const loading = ref(false);
const saving = ref(false);
const serverError = ref<string | null>(null);
const activeTab = ref<string>('general');

const typeOptions = [
    { label: 'String', value: 'string' },
    { label: 'Integer', value: 'integer' },
    { label: 'Boolean', value: 'boolean' },
    { label: 'JSON', value: 'json' },
];

const addOpen = ref(false);
const newSetting = reactive<Setting>({
    key: '',
    value: '',
    type: 'string',
    group: 'general',
});

const knownGroups = computed(() => {
    const seen = new Set<string>();
    groups.value.forEach((g) => seen.add(g.name));
    ['general', 'branding', 'security', 'announcements'].forEach((g) => seen.add(g));
    return Array.from(seen).map((g) => ({ label: g, value: g }));
});

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/platform/settings');
        groups.value = data.groups ?? [];
        ensureDefaultTabs();
    } finally {
        loading.value = false;
    }
}

function ensureDefaultTabs() {
    const have = new Set(groups.value.map((g) => g.name));
    for (const g of ['general', 'branding', 'security', 'announcements']) {
        if (!have.has(g)) {
            groups.value.push({ name: g, settings: [] });
        }
    }
    if (groups.value.length && !groups.value.some((g) => g.name === activeTab.value)) {
        activeTab.value = groups.value[0].name;
    }
}

function normalizeForSave(s: Setting): any {
    let value: any = s.value;
    if (s.type === 'json' && typeof value === 'string') {
        try { value = JSON.parse(value); } catch { /* keep raw */ }
    }
    if (s.type === 'integer' && value !== null && value !== '') {
        value = Number(value);
    }
    if (s.type === 'boolean') {
        value = !!value;
    }
    return {
        key: s.key,
        value,
        type: s.type,
        group: s.group,
    };
}

async function saveAll() {
    saving.value = true;
    serverError.value = null;
    try {
        const payload = groups.value.flatMap((g) => g.settings.map(normalizeForSave));
        if (!payload.length) {
            serverError.value = 'Nothing to save.';
            return;
        }
        const { data } = await api.put('/api/v1/platform/settings', { settings: payload });
        groups.value = data.groups ?? [];
        ensureDefaultTabs();
    } catch (e: any) {
        serverError.value = e?.response?.data?.message ?? 'Save failed';
    } finally {
        saving.value = false;
    }
}

function openAdd() {
    newSetting.key = '';
    newSetting.value = '';
    newSetting.type = 'string';
    newSetting.group = activeTab.value || 'general';
    addOpen.value = true;
}

function addNow() {
    if (!newSetting.key.trim()) return;
    let group = groups.value.find((g) => g.name === newSetting.group);
    if (!group) {
        group = { name: newSetting.group, settings: [] };
        groups.value.push(group);
    }
    group.settings.push({ ...newSetting });
    activeTab.value = newSetting.group;
    addOpen.value = false;
}

function confirmDelete(groupName: string, setting: Setting) {
    confirm.require({
        message: `Delete setting "${setting.key}"?`,
        header: 'Delete setting',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Delete',
        rejectLabel: 'Cancel',
        acceptProps: { severity: 'danger' },
        accept: async () => {
            if (setting.id) {
                await api.delete('/api/v1/platform/settings', { data: { key: setting.key } });
            }
            const group = groups.value.find((g) => g.name === groupName);
            if (group) group.settings = group.settings.filter((s) => s.key !== setting.key);
        },
    });
}

function jsonStringify(v: any): string {
    if (v === null || v === undefined) return '';
    if (typeof v === 'string') return v;
    try { return JSON.stringify(v, null, 2); } catch { return String(v); }
}

onMounted(load);
</script>

<template>
    <ConfirmDialog />

    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <div class="font-semibold text-2xl">Platform Settings</div>
                <p class="text-muted-color m-0">Platform-wide settings (tenant_id IS NULL). Tenants can't see or edit these.</p>
            </div>
            <div class="flex gap-2">
                <Button label="Add setting" icon="pi pi-plus" outlined @click="openAdd" />
                <Button label="Save all" icon="pi pi-check" :loading="saving" @click="saveAll" />
            </div>
        </div>

        <Message v-if="serverError" severity="error" :closable="false" class="mb-3">{{ serverError }}</Message>

        <div v-if="loading" class="text-muted-color">Loading…</div>

        <Tabs v-else v-model:value="activeTab">
            <TabList>
                <Tab v-for="group in groups" :key="group.name" :value="group.name">
                    <span class="capitalize">{{ group.name }}</span>
                    <span class="text-muted-color text-xs ml-2">({{ group.settings.length }})</span>
                </Tab>
            </TabList>
            <TabPanels>
                <TabPanel v-for="group in groups" :key="group.name" :value="group.name">
                    <div v-if="group.settings.length === 0" class="text-muted-color">
                        No settings in this group yet. Click "Add setting" above.
                    </div>
                    <div v-else class="flex flex-col gap-4">
                        <div
                            v-for="setting in group.settings"
                            :key="setting.key"
                            class="border border-surface-200 dark:border-surface-700 rounded p-3"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <div class="font-mono text-sm">{{ setting.key }}</div>
                                    <div v-if="setting.description" class="text-muted-color text-xs">{{ setting.description }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-color">type: {{ setting.type }}</span>
                                    <Button icon="pi pi-trash" severity="danger" text rounded @click="confirmDelete(group.name, setting)" />
                                </div>
                            </div>

                            <InputText
                                v-if="setting.type === 'string'"
                                v-model="setting.value"
                                class="w-full"
                            />
                            <InputNumber
                                v-else-if="setting.type === 'integer'"
                                v-model="setting.value"
                                :use-grouping="false"
                                class="w-full"
                            />
                            <ToggleSwitch
                                v-else-if="setting.type === 'boolean'"
                                v-model="setting.value"
                            />
                            <Textarea
                                v-else
                                :model-value="jsonStringify(setting.value)"
                                @update:model-value="(v: any) => (setting.value = v)"
                                class="w-full font-mono text-xs"
                                rows="6"
                            />
                        </div>
                    </div>
                </TabPanel>
            </TabPanels>
        </Tabs>

        <Dialog v-model:visible="addOpen" header="Add setting" modal :style="{ width: 'min(540px, 95vw)' }">
            <div class="flex flex-col gap-3">
                <div>
                    <label class="block mb-1 font-medium">Key *</label>
                    <InputText v-model="newSetting.key" class="w-full" placeholder="e.g. platform.support_email" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1 font-medium">Group</label>
                        <Select
                            v-model="newSetting.group"
                            :options="knownGroups"
                            option-label="label"
                            option-value="value"
                            editable
                            class="w-full"
                        />
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Type</label>
                        <Select
                            v-model="newSetting.type"
                            :options="typeOptions"
                            option-label="label"
                            option-value="value"
                            class="w-full"
                        />
                    </div>
                </div>
                <div>
                    <label class="block mb-1 font-medium">Value</label>
                    <InputText v-if="newSetting.type === 'string'" v-model="newSetting.value" class="w-full" />
                    <InputNumber v-else-if="newSetting.type === 'integer'" v-model="newSetting.value" :use-grouping="false" class="w-full" />
                    <ToggleSwitch v-else-if="newSetting.type === 'boolean'" v-model="newSetting.value" />
                    <Textarea v-else v-model="newSetting.value" class="w-full font-mono text-xs" rows="6" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" severity="secondary" outlined @click="addOpen = false" />
                <Button label="Add" icon="pi pi-plus" @click="addNow" />
            </template>
        </Dialog>
    </div>
</template>
