<template>
  <form id='frmKanban' ref="frmKanban" autocomplete="off" @submit="formSubmit($event)">
    <input type="hidden" name="mode" :value="mode">
    <input type="hidden" name="idx" :value="kanban.idx" v-if="mode != 'add'">
    <dl>
    <dt>작업구분</dt>
    <dd>

    <input type="radio" name="status" id='status_ready' value="ready" v-model="picked">
    <label for='status_ready'>준비중</label>

    <input type="radio" name="status" id='status_progress' value="progress" v-model="picked">
    <label for='status_progress'>진행중</label>

    <input type="radio" name="status" id='status_done' value="done" v-model="picked">
    <label for='status_done'>완료</label>
    </dd>
      </dl>
      <dl>
    <dt>작업명</dt>
    <dd>
    <input type="text" name="subject" :value="kanban.subject">
    </dd>
    </dl>
    <dl>
      <dt>작업내용</dt>
      <dd>
        <textarea name="content" :value="kanban.content"></textarea>
    </dd>
    </dl>
    <input type="submit" value="작업등록" v-if="mode == 'add'">
    <input type="submit" value="작업수정" v-else>
    </form>
    <MessagePopup ref='popup' :message="message" />
</template>
<script>
import kanban from "../../models/kanban.js"
import MessagePopup from "../../components/common/Message.vue"
export default {
    mixins : [kanban],
    components : {MessagePopup},
    data() {
        return {
            message : "",
        };
    },
    computed : {
       picked() {
           return this.kanban.status || "ready";
       }
    },
    props : {
        mode : {
            type : String,
            default : "add",
        },
        kanban : {
            type : Object,
            default() {
                return {
                    idx : 0,
                    status : "ready",
                    subject : "",
                    content : "",
                };
            }
        }
    },
    methods : {
        async formSubmit(e) {
            e.preventDefault();
           const formData = new FormData(this.$refs.frmKanban);
           let result = {};
           let idx = 0;
           if (this.mode == 'add') { // 작업 추가
                result = await this.$addWork(formData);
                idx = result.data.idx;
           } else { // 작업 수정
                result = await this.$editWork(formData);
                idx = this.$route.query.idx;
           }

            if (result.success) {
                this.$router.push({ path : "/kanban/view", query : { idx }});
                return;
            }

           if (result.message) {
               this.$showMessage(this, result.message);
           }
        }
    }
}
</script>
