import axios from 'axios'

export default {
    methods : {
        async $request(url, data, method) {
            method = method || "GET";
            if (data && data instanceof FormData) {
                data = this.$formDataToJson(data);
            }
	
            if (this.$isLogin()) {
                data.memNo = this.$getMember().memNo;
            }
            
            data.origin = "front";
            try {
                const result = await axios({
                    method,
                    url,
                    data,
                });
                return result.data;
            } catch (err) {
                console.error(err);
                return false;
            }
        },
        /**
         * FormData 형식 데이터 -> json 
         * @param {*} data 
         */
        $formDataToJson(data) {
            const json = {};
            for (let field of data.entries()) {
                json[field[0]] = field[1];
            }

            return json;
        },
        $showMessage(obj, message) {
            obj.message = message;
            const ref = obj.$refs.popup;
            if(ref) {
                ref.isHide = false;
            }
        },
        /**
         * 로그인 회원 정보 유지 
         * 
         */
        async $loginInit() {
            
            if (this.$store.state.member) {
                return;
            }
            
            // 회원 정보가 없는 경우만 서버에 정보 요청 
            const token = sessionStorage.getItem("sessionId");
            if (!token) {
                return;
            }
           
            const apiURL = this.$store.state.apiURL + "/member/index.php";
            const data = { mode : "get_member", token };
            const result = await this.$request(apiURL, data, "POST");
            
            if (result.success) {
                this.$store.commit('setMember', result.data);
            }
        },
        /** 로그인 체크 */
        $isLogin() {
            return this.$store.state.member?true:false;
        },
        /** 로그아웃 처리 */
        $logOut() {
            this.$store.commit('setMember', null);
            sessionStorage.removeItem('sessionId');
        },
        /** 로그인 회원 정보 */
        $getMember() {
            return this.$store.state.member;
        },
        /** 세션 스토리지 sessionId */
        $getToken() {
            return sessionStorage.getItem("sessionId");
        }
    }
}