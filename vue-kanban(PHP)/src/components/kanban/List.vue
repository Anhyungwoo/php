<template>
    <div class='stit'>{{ title }}</div>
    <ul class='work_list'>
        <li :key="i" v-for="(li, i) in list" @click="goView(li.idx)">{{ li.subject }}</li>
    </ul>
</template>
<script>
import kanban from "../../models/kanban.js"
export default {
    mixins: [kanban],
    data() {
        return {
            title : "",
            list : [],
        };
    },
    props : {
        status : {
            type : String,
            default : "ready",
        }
    },
    async mounted() {
        switch(this.status) {
            case "progress" : 
                this.title = "진행중";
                break;
            case "done" : 
                this.title = "완료";
                break;
            default : 
                this.title = "준비중";
        }

        this.list = await this.$getList(this.status);
    },
    methods : {
        goView(idx) {
            this.$router.push({ path : "/kanban/view", query : { idx }});
        }
    }
}
</script>