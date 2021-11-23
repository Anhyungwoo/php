import store from "./common/store.js"
import boot from "./bootstrap.js"
import lib from "./common/lib.js"
export default (app) => {
    app.use(store); // 공통 저장소
    app.mixin(lib); // 공통 함수
    app.mixin(boot); // 초기 공통 처리 
};