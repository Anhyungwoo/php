export default {
    data() {
        return {
            requestURL : this.$store.state.apiURL + "/kanban/index.php",
        };
    },
    methods : {
        /** 작업 추가 */
        async $addWork(data) {
           const result = await this.$request(this.requestURL, data, "POST");
           return result;
        },
        /** 작업 수정 */
        async $editWork(data) {
            const result = await this.$request(this.requestURL, data, "POST");
            return result;
        },
        /** 작업 삭제 */
       async $deleteWork(idx) {
            const data = { mode : "delete", idx };
            const result = await this.$request(this.requestURL, data, "POST");
            return result;
        },
        /** 작업 목록  */
        async $getList(status) {
            const data = { mode : "getList", status };
            const result = await this.$request(this.requestURL, data, "POST");
            const list = result.data || [];
            return list;
        },
        /**
         * 작업 내용 조회
         * @param {*} idx 
         */
        async $get(idx) {
            const data = {
                mode : "get",
                idx,
            };
            const result = await this.$request(this.requestURL, data, 
                "POST");
            return result;
        }
    }
}