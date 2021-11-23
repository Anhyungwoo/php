<template>
    <PageTitle>작업등록</PageTitle>
    <Form />
</template>
<script>
import PageTitle from "../../components/PageTitle.vue"
import Form from "../../components/kanban/Form.vue"
export default {
    components: { PageTitle, Form },
    created() {
        if (!this.$isLogin()) {
            return this.$router.push({ path : "/login"});
        }
    }
}
</script>