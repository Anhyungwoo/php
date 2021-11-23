<template>
    <PageTitle>회원가입</PageTitle>
    <Form :mode="mode" />
</template>
<script>
import PageTitle from "../../components/PageTitle.vue"
import Form from "../../components/member/Form.vue"
export default {
    components: {PageTitle, Form},
    created() {
        if (this.$isLogin()) {
            this.$router.push({ path : "/my_info" })
        }
    },
    data() {
        return {
            mode : "join"
        };
    }
}
</script>