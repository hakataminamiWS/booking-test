<template>
    <v-container>
        <v-card class="mx-auto" max-width="1200">
            <v-card-title
                class="text-h5 font-weight-bold d-flex justify-space-between align-center"
            >
                <span>契約管理</span>
                <v-btn color="primary" :href="createContractUrl"
                    >新規契約追加</v-btn
                >
            </v-card-title>
            <v-divider></v-divider>

            <v-card-text>
                <v-row>
                    <v-col cols="12" md="4">
                        <v-select
                            v-model="selectedStatus"
                            :items="statusOptions"
                            label="ステータスで絞り込み"
                            dense
                            outlined
                            hide-details
                        ></v-select>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-select
                            v-model="endDateFilterMode"
                            :items="endDateFilterOptions"
                            label="契約終了日で絞り込み"
                            dense
                            outlined
                            hide-details
                        ></v-select>
                    </v-col>
                    <v-col
                        v-if="endDateFilterMode === '指定日以前'"
                        cols="12"
                        md="4"
                    >
                        <v-text-field
                            v-model="specifiedEndDate"
                            label="終了日を指定"
                            type="date"
                            dense
                            outlined
                            hide-details
                        ></v-text-field>
                    </v-col>
                </v-row>

                <v-data-table
                    :headers="headers"
                    :items="filteredContracts"
                    item-value="id"
                    class="elevation-1 mt-4"
                    hover
                    @click:row="goToContract"
                    no-data-text="契約情報がありません。"
                >
                </v-data-table>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";

interface Contract {
    id: number;
    user: { owner: { name: string } };
    max_shops: number;
    status: string;
    start_date: string;
    end_date: string;
}

const props = defineProps<{
    contracts: Contract[];
}>();

// Filter refs
const selectedStatus = ref("すべて");
const statusOptions = ["すべて", "active", "inactive"];

const endDateFilterMode = ref("すべて");
const endDateFilterOptions = ["すべて", "契約終了済み", "指定日以前"];
const specifiedEndDate = ref<string | null>(null);

const filteredContracts = computed(() => {
    let items = props.contracts;

    // Status filter
    if (selectedStatus.value !== "すべて") {
        items = items.filter(
            (contract) => contract.status === selectedStatus.value
        );
    }

    // End date filter
    if (endDateFilterMode.value === "契約終了済み") {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        items = items.filter((contract) => new Date(contract.end_date) < today);
    } else if (
        endDateFilterMode.value === "指定日以前" &&
        specifiedEndDate.value
    ) {
        const specifiedDate = new Date(specifiedEndDate.value);
        specifiedDate.setHours(0, 0, 0, 0);
        items = items.filter(
            (contract) => new Date(contract.end_date) <= specifiedDate
        );
    }

    return items;
});

const headers = [
    { title: "ID", key: "id", width: "10%" },
    { title: "オーナー名", key: "user.owner.name" },
    { title: "最大店舗数", key: "max_shops" },
    { title: "ステータス", key: "status" },
    { title: "契約開始日", key: "start_date" },
    { title: "契約終了日", key: "end_date" },
];

const createContractUrl = "/admin/contracts/create";

const goToContract = (_event: any, { item }: { item: Contract }) => {
    window.location.href = `/admin/contracts/${item.id}`;
};
</script>
