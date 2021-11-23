# 칸반보드
## 개요
### 1. 서비스 내용
### 2. 적용 기술
### 3. 각 페이지별 소개
### 4. 핵심기술

--------------------------



### 1. 서비스 내용
```
게시글을 업로드, 수정, 삭제후 목록에서 확인할 수 있는 게시판을 개발했으며,
게시글의 기능들은 모두 회원가입 후 로그인 상태여야만 사용 가능합니다.
```

* PHP로 구현된 칸반 보드 (http://anstar94.cafe24.com)
* Node.js로 구현된 칸반 보드 (http://ahw94.cafe24app.com)

### 2. 적용 기술

```
 PHP-Kanban : Html / Css / JavaScript / Vue.js / PHP / DB

 Node.js-Kanban : Html / Css / JavaScript / vue.js / Node.js / DB
```

### 3. 각 페이지별 소개

* 로그인
```
프론트엔드(Vue.js)에서 회원데이터를 전송하여 백엔드(PHP)에서 요청받는 데이터가
DB에 등록된 회원의 정보와 일치하는지 검토하여 일치하는 데이터가 있으면 로그인이 됩니다.
로그인 유지를 처리하기 위해서 sessionId 형태로 토큰을 저장하여 로그인 유지 처리를 하고
페이지 새로고침 시 데이터유지가 안되는걸 방지하기 위해 vuex-presistedstate Module을
설치하여 유지하도록 개발했습니다.
```

* 회원가입
```
회원가입은 사용자가 데이터를 입력하면 연결된 DB를 통해 데이터가 저장됩니다.
그리고 회원가입시 사용자가 일치하지 않는 데이터를 입력시, 모두 예외 처리를 했습니다.
사용자가 입력해야 하는 일치하는 데이터는 다음과 같습니다.
1. 아이디 : 6자 이상 알파벳과 숫자로 구성
2. 비밀번호 : 8자 이상 알파벳 + 특수문자 + 숫자로 구성
3. 비밀번호 확인 : 앞서 입력한 비밀번호와 일치해야 합니다.
4. 회원명과 휴대전화번호는 필수 입력사항이 아니므로 작성하지 않아도 가입이 가능합니다.
```

* 회원정보 수정
```
회원정보수정 부분은 토큰값과 회원명을 확인하여 정보를 가져와서 일치하면 수정이 가능합니다.
또한 비밀번호 변경시 입력된 경우만 비밀번호가 변경되도록 처리하고 미입력시 기존 비밀번호가
유지되도록 개발했습니다.
```

* 게시판 작성, 수정, 삭제
```
프론트엔드(Vue.js) 기술을 사용하여 게시판의 기본 프레임을 만들고, 백엔드(PHP, DB)를 연동하여
사용자가 입력하는 데이터가 데이터베이스 테이블에 저장이 되도록 연동시켰습니다.
```
--------------------------


## 핵심기술
### 1. 회원가입
```
회원가입은 사용자가 원하는 데이터를 요청받아 DB를 연동시켜 저장합니다.
이때 비밀번호는 개발자 및 관리자가 알 필요가 없으므로 암호해쉬값으로 입력됩니다.
```

* 회원가입 기본 프레임 (Form.vue)
```
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
```

* 회원가입 메소드 및 DB연동
```
public function join($data) {
	$this->checkJoinData($data);

	$hash = password_hash($data['memPw'], PASSWORD_DEFAULT, ["cost" => 10]);

	$cellPhone = "";
	if ($data['cellPhone']) {
		$cellPhone = preg_replace("/[^0-9]/", "", $data['cellPhone']);
	}

	$sql = "INSERT INTO member (memId, memPw, memNm, cellPhone)
				VALUES (:memId, :memPw, :memNm, :cellPhone)";
	$stmt = $this->db->prepare($sql);
	$stmt->bindValue(":memId", $data['memId']);
	$stmt->bindValue(":memPw", $hash);
	$stmt->bindValue(":memNm", $data['memNm']);
	$stmt->bindValue(":cellPhone", $cellPhone);
	$result = $stmt->execute();
	if (!$result) { // SQL 실행 실패 -> SQL 오류
		$errorInfo = $this->db->errorInfo();
		throw new Exception(implode("/", $errorInfo));
	}

	$memNo = $this->db->lastInsertId();
	$memberInfo = $this->get($memNo);

	return $memberInfo;
}
```
### 2. 로그인
```
로그인은 PHP에서 요청받는 데이터가 DB Table에 저장된 회원의 데이터와 일치하면
게시판으로 이동하여 게시글의 여러 기능을 사용할 수 있습니다.
```
* 로그인 기본 프레임 (Login.vue)
```
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
```
* 로그인 처리 부분 - 데이터에 있는 Id와 Pw의 키값이 없을경우 예외 처리
```
/** 로그인 처리 */
public function login($data) {

	if (!isset($data['memId']) || !$data['memId']) {
		throw new Exception("아이디를 입력하세요.");
	}

	if (!isset($data['memPw']) || !$data['memPw']) {
		throw new Exception("비밀번호를 입력하세요.");
	}
```

* 토큰 발급으로 로그인을 유지시키고 유효시간을 2시간으로 설정
```
/**
* 토큰 발급
*/
public function generateToken($memId) {
	$token = md5(uniqid(true));

	$expireTime = time() + 60 * 60 * 2;
	$date = date("Y-m-d H:i:s", $expireTime);
	$sql = "UPDATE member
					SET
						token = :token,
						tokenExpires = :tokenExpires
				WHERE
					memId = :memId";
	$stmt = $this->db->prepare($sql);
	$stmt->bindValue(":token", $token);
	$stmt->bindValue(":tokenExpires", $date);
	$stmt->bindValue(":memId", $memId);
	$result = $stmt->execute();
	if (!$result) {
		return false;
	}

	return $token;
}
```

### 3. 게시판
```
게시판의 기능들은 로그인 후 회원인 경우에만 접근 가능하고 사용자가 입력한 데이터를
DB에 요청시켜 저장, 삭제, 수정이 가능하도록 개발하였습니다.
```
* 게시판 기본 프레임 (Form.vue)
```
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
```
* 게시글 등록 DB연동 부분
```
/** 작업 추가 */
public function addWork($data) {
	$this->checkData($data); // 데이터 유효성 검사

	$sql = "INSERT INTO worklist (memNo, status, subject, content)
						VALUES (:memNo, :status, :subject, :content)";
	$stmt = $this->db->prepare($sql);
	$stmt->bindValue(":memNo", $data['memNo'], PDO::PARAM_INT);
	$stmt->bindValue(":status", $data['status']);
	$stmt->bindValue(":subject", $data['subject']);
	$stmt->bindValue(":content", $data['content']);
	$result = $stmt->execute();
	if (!$result) {
		return false;
	}

	$idx = $this->db->lastInsertId();

	return $idx;
}
```
