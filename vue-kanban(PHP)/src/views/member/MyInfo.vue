<template>
    <PageTitle>회원정보수정</PageTitle>
    <Form :mode="mode" :member="member" />
</template>
<script>
import PageTitle from "../../components/PageTitle.vue"
import Form from "../../components/member/Form.vue"
export default {
    components: {PageTitle, Form},
    created() {
        if (!this.$isLogin()) {
            this.$router.push({ path : "/login" })
        }
    },
    computed: {
        member() {
            return this.$getMember();
        }
    },
    data() {
        return {
            mode : "update"
        };
    }
}
</script>