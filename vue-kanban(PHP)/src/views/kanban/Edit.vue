<template>
    <PageTitle>작업수정</PageTitle>
    <Form :mode="mode" :kanban="kanban" />
</template>
<script>
import PageTitle from "../../components/PageTitle.vue"
import Form from "../../components/kanban/Form.vue"
import kanban from "../../models/kanban.js"
export default {
    components: { PageTitle, Form },
    mixins: [kanban],
    data() {
        return {
            mode : "edit",
            kanban : {},
        };
    },
    created() {
        if (!this.$isLogin()) {
            return this.$router.push({ path : "/login"});
        }
    },
    async mounted() {
        const idx = this.$route.query.idx;
        const result = await this.$get(idx);
        if (result.success) {
            this.kanban = result.data;
        }
    }
}
</script>