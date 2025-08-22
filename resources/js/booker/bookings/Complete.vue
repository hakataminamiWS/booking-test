<template>
  <div class="container">
    <h1>予約完了</h1>

    <div v-if="status" class="alert alert-success" role="alert">
        {{ status }}
    </div>

    <p>ご予約いただき、誠にありがとうございました。</p>

    <div class="mt-4">
        <a :href="homeUrl" class="v-btn v-btn--elevated v-theme--light bg-primary v-btn--density-default v-btn--size-default v-btn--variant-elevated">
            トップページに戻る
        </a>
    </div>

    <div class="mt-10 pt-10 border-t">
        <h2>今後のご予約を簡単にするために</h2>
        <p>今回ご入力いただいた情報でユーザー登録をしますか？</p>
        <!-- 将来的にここにユーザー登録フォームなどを実装 -->
        <v-btn class="mt-4">ユーザー登録に進む（実装予定）</v-btn>
    </div>

  </div>
</template>

<script setup lang="ts">

defineProps<{
  status: string;
  homeUrl: string;
}>();

</script>

<style scoped>
.alert {
    padding: 1rem;
    border: 1px solid transparent;
    border-radius: .25rem;
    margin-bottom: 1rem;
}
.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}
.border-t {
    border-top: 1px solid #ccc;
}
</style>
