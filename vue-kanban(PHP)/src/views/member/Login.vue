<template>
    <PageTitle>로그인</PageTitle>
    <form ref="frmLogin" autocomplete="off" @submit="formSubmit($event)">
        <input type="text" name="memId" placeholder="아이디" v-model="memId"><br>
        <input type="password" name="memPw" placeholder="비밀번호" v-model="memPw"><br>
        <input type="submit" value="로그인">
    </form>
    <MessagePopup ref='popup' :message="message" />
</template>
<script>
import PageTitle from '../../components/PageTitle.vue'
import MessagePopup from '../../components/common/Message.vue'
import member from '../../models/member.js'
export default {
    components : {PageTitle, MessagePopup},
    mixins : [member],
    created() {
        if (this.$isLogin()) {
            this.$router.push({ path : "/logout"} );
        }
    },
    data() {
        return {
            message : "",
            memId : "",
            memPw : "",
        };
    },
    methods : {
        async formSubmit(e) {
            e.preventDefault();
            const formData = new FormData(this.$refs.frmLogin);
            const result = await this.$login(formData);
            if (result.success) {
                this.memId = "";
                this.memPw = "";
               this.$router.push({ path : "/kanban/list"});
            }

            if (result.message) {
                this.$showMessage(this, result.message);
            }
        }
    }

}
</script>