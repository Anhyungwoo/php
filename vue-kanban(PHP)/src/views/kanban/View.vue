<template>
<PageTitle>작업내용확인</PageTitle>
<div class='work_view'>
    <dl>
        <dt>작업구분</dt>
        <dd v-if="view.status == 'progress'">진행중</dd>
        <dd v-else-if="view.status == 'done'">완료</dd>
        <dd v-else>준비중</dd>
    </dl>
    <dl>
        <dt>등록일</dt>
        <dd>{{ view.regDt }}</dd>
    </dl>
    <dl>
        <dt>작업명</dt>
        <dd>{{ view.subject }}</dd>
    </dl>
    
    <div v-html="view.contentHtml" class='content'></div>
    
    <div class='btns'>
        <button type="button" @click="goLink('add')">추가</button>
        <button type="button" @click="goLink('edit')">수정</button>
        <button type="button" @click="deleteWork">삭제</button>
        <button type="button" @click="goLink('list')">목록</button>
    </div>
</div>

<MessagePopup ref="popup" :message="message" />
</template>
<script>
import PageTitle from "../../components/PageTitle.vue"
import MessagePopup from "../../components/common/Message.vue"
import kanban from "../../models/kanban.js"
export default {
    components : {PageTitle, MessagePopup},
    mixins : [kanban],
    data() {
        return {
            message : "",
            view : {},
        };
    },
    async mounted() {
        const idx = this.$route.query.idx;
        const result = await this.$get(idx);
        if (result.success) {
            this.view = result.data;
        }

        if (result.message) {
            this.$showMessage(this, result.message);
        }
    },
    methods : {
        /** 링크 이동  */
        goLink(link) {
            this.$router.push({ path : "/kanban/" + link, query : { idx : this.view.idx }});
        },
        async deleteWork() {
            if (!confirm('정말 삭제하시겠습니까?')) {
                return;
            }

            const idx = this.view.idx;
            const result = await this.$deleteWork(idx);
            if (result.success) {
                this.$router.push({ path : "/kanban/list" });
            }

            if (result.message) {
                this.$showMessage(this, result.message);
            }
        }
    }
}
</script>