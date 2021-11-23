<template>
<form ref="frmMember" method="post" autocomplete='off' @submit="formSubmit($event)">
    <input type="hidden" name="mode" :value="mode">
    <input type="text" name="memId" placeholder='아이디' :value="member.memId" v-if="mode == 'join'">
    <div v-else class='stit'>아이디 : {{ member.memId }}</div>
    <br>          
    <input type="password" name="memPw" placeholder='비밀번호'><br>        
    <input type="password" name="memPwRe" placeholder='비밀번호확인'><br>
    <input type="text" name="memNm" placeholder='회원명' :value="member.memNm"><br>
    <input type="text" name="cellPhone" placeholder="휴대전화번호" :value="member.cellPhone"><br>
    <input type="submit" value="가입하기" v-if="mode == 'join'">
    <input type="submit" value="수정하기" v-else>
</form>
<MessagePopup ref='message_popup' :message="message" />
</template>
<script>
import member from "../../models/member.js"
import MessagePopup from "../../components/common/Message.vue"
export default {
    mixins : [member],
    components : {MessagePopup},
    data() {
        return {
            isHide : true,
            message : "",
        };
    },
    props : {
        mode : {
            type : String,
            default : "join"
        },
        member : {
            type : Object,
            default() {
                return {
                    memId : "",
                    memNm : "",
                    cellPhone : ""
                };
            }
        }
    },
    methods : {
        async formSubmit(e) {
            e.preventDefault();
            const formData = new FormData(this.$refs.frmMember);
            let result = {};
            if (this.mode == 'join') {  // 회원 가입 
                result = await this.$join(formData);
                if (result.success) {
                    this.$router.push({ path : '/login'});
                }
            } else { // 회원 정보 수정
                result = await this.$update(formData);
                if (result.success) {
                    const frm = this.$refs.frmMember;
                    frm.memPw.value = "";
                    frm.memPwRe.value = "";
                }
            }
            if (result.message) {
                this.showMessage(result.message);
                
            }
        },
        showMessage(message) {
            this.$refs.message_popup.isHide = false;
            this.message = message;
        }
    }
}
</script>