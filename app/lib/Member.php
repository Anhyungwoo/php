<?php
/**
* 회원가입, 로그인 처리
*
*/
class Member {
	private $db; // DB 인스턴스
	private static $instance;

	private function __construct() {
		$this->db = DB::getInstance();
	}

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new Member();
		}

		return self::$instance;
	}

	/** 회원 가입 */
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
		$result = $stmt->execute(); // true, false
		if (!$result) { // SQL 실행 실패 -> SQL 오류
			$errorInfo = $this->db->errorInfo();
			throw new Exception(implode("/", $errorInfo));
		}

		$memNo = $this->db->lastInsertId();
		$memberInfo = $this->get($memNo);

		return $memberInfo;
	}

	/** 회원정보 수정 */
	public function update($data) {
		/** 필수 항목 체크 S */
		$required = [
			'token' => "토큰이 누락되었습니다.",
			'memNm' => "회원명을 입력하세요.",
		];

		foreach ($required as $key => $msg) {
			if (!isset($data[$key]) || ($data[$key] && trim($data[$key]) == "")) {
				throw new Exception($msg);
			}
		}
		/** 필수 항목 체크 E */

		/** 비밀번호 변경 시도 시 -> 비밀번호 유효성 검사 */
		$this->checkPassword($data['memPw'], $data['memPwRe']);

		/** 휴대전화번호 형식 체크 */
		$this->checkCellPhone($data['cellPhone']);

		$addSet = $hash = "";
		if ($data['memPw']) {
			$hash = password_hash($data['memPw'], PASSWORD_BCRYPT, ["cost" => 10]);
			$addSet = ",memPw = :hash";
		}

		$sql = "UPDATE member
						SET
							memNm = :memNm,
							cellPhone = :cellPhone
							{$addSet}
					WHERE
						token = :token";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":memNm", $data['memNm']);
		$stmt->bindValue(":cellPhone", $data['cellPhone']);
		$stmt->bindValue(":token", $data['token']);
		if ($hash) { // 비밀번호를 변경하는 경우
			$stmt->bindValue(":hash", $hash);
		}
		$result = $stmt->execute();
		if (!$result) {
			$errorInfo = $this->db->errorInfo();
			throw new Exception(implode("/", $errorInfo));
		}

		return $result;
	}

	/** 로그인 처리 */
	public function login($data) {

		if (!isset($data['memId']) || !$data['memId']) {
			throw new Exception("아이디를 입력하세요.");
		}

		if (!isset($data['memPw']) || !$data['memPw']) {
			throw new Exception("비밀번호를 입력하세요.");
		}

		// 회원정보 조회
		$info = $this->get($data['memId'], true);// 비번 비교가 필요 하므로 2번째 매개변수 true
		if (!$info) {
			throw new Exception("존재하지 않는 회원입니다.");
		}

		// 비밀번호 체크
		$match = password_verify($data['memPw'], $info['memPw']);
		if (!$match) {
			throw new Exception("비밀번호가 일치하지 않습니다.");
		}

		// 토큰 발급 -> vue.js에서 로그인 유지 용도로 사용(유효시간...)
		$token = $this->generateToken($data['memId']);
		return $token;
	}

	/**
	* 회원가입 유효성 검사
	*
	*   1. 필수 항목 체크(memId, memPw, memPwRe, memNm) - O
	*   2. 아이디 체크(자리수 6자리 이상, 알파벳 + 숫자) - O
	*   3. 중복 아이디 체크 - O
	*   4. 비밀번호 체크(자리수 8자리 이상, 알파벳 + 숫자 + 특수 문자) - O
	*   5. 비밀번호 확인 - O
	*   6. 휴대전화번호는 필수 X -> 입력된 경우는 휴대전화번호 형식 체크
	*/
	public function checkJoinData($data) {
		// 필수 항목 체크
		$required = [
			"memId" => "아이디를 입력하세요.",
			"memPw" => "비밀번호를 입력하세요.",
			"memPwRe" => "비밀번호를 확인하세요.",
			"memNm" => "회원명을 입력하세요.",
		];

		foreach ($required as $key => $msg) {
			if (!isset($data[$key]) || trim($data[$key]) == "") {
				throw new Exception($msg);
			}
		}

		/** 아이디 체크 S */
		$memId = $data['memId'];
		if (strlen($memId) < 6) {
			throw new Exception("아이디는 6자리 이상 입력하세요.");
		}

		// 아이디가 알파벳, 숫자로만 구성
	  if (!preg_match("/[0-9]/", $memId) || !preg_match("/[a-z]/i", $memId)) { // 숫자 + 알파벳이 아닌 문자가 포함되어 있으면 true
			throw new Exception("아이디는 알파벳과 숫자를 조합하여 입력하세요.");
		}
		/** 아이디 체크 E */

		/** 중복 아이디 체크 S */
		$sql = "SELECT COUNT(*) cnt FROM member WHERE memId = :memId";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":memId", $memId);
		$result = $stmt->execute(); // true/false
		if (!$result) {
			$errorInfo = $this->db->errorInfo();
			throw new Exception(implode("/", $errorInfo));
		}

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($row['cnt'] > 0) { // 이미 가입된 경우
			throw new Exception("이미 가입된 아이디 입니다. - ".$memId);
		}
		/** 중복 아이디 체크 E */

		// 비밀번호 체크
		$this->checkPassword($data['memPw'], $data['memPwRe']);

		// 휴대전화번호 형식 체크
		$this->checkCellPhone($data['cellPhone']);
	}

	/** 비밀번호 체크 */
	public function checkPassword($memPw, $memPwRe) {
		if (!$memPw) { // 수정시에는 입력된 경우만 처리하므로
			return;
		}

		if (strlen($memPw) < 8) {
			throw new Exception("비밀번호는 8자리 이상 입력하세요.");
		}

		if (!preg_match("/[0-9]/", $memPw) || !preg_match("/[a-z]/i", $memPw) || !preg_match("/[~!@#$%^&*()]/", $memPw)) {
			throw new Exception("비밀번호는 1개이상 알파벳, 숫자, 특수문자로 입력하세요.");
		}

		// 비밀번호 확인
		if ($memPw != $memPwRe) {
			throw new Exception("비밀번호를 확인하세요.");
		}
	}

	/** 휴대전화번호 형식 체크 */
	public function checkCellPhone($cellPhone) {
		if (!$cellPhone) { // 휴대전화번호는 필수 항목 아니므로 값이 입력된 경우만 체크
			return;
		}

		$cellPhone = preg_replace("/[^0-9]/", "", $cellPhone);
		$pattern = "/^01[016789][0-9]{3,4}[0-9]{4}$/";
		if (!preg_match($pattern, $cellPhone)) {
			throw new Exception("휴대전화번호 형식이 아닙니다.");
		}
	}

	/**
	* 회원정보 조회
	*
	* @param $memNo - 정수(is_numeric) - 회원번호, 문자 - 아이디
	* @param $isLogin - 로그인이 되어 있는 경우는 memPw를 정보로 제공 X
	*/
	public function get($memNo, $isLogin = false) {
		$field = "memNo";
		if (!is_numeric($memNo)) { // 숫자가 아니면 -> 회원 아이디
			$field = "memId";
		}

		$sql = "SELECT * FROM member WHERE {$field} = :{$field}";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":{$field}", $memNo, is_numeric($memNo)?PDO::PARAM_INT :PDO::PARAM_STR);
		$result = $stmt->execute(); // true, false
		if (!$result) {
			$errorInfo = $this->db->errorInfo();
			throw new Exception(implode("/", $errorInfo));
		}

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$row) { // 회원이 없는 경우
			return false;
		}

		if (!$isLogin) { // 로그인 용도가 아니라면 민감한 데이터인 memPw 제외
			unset($row['memPw']);
		}

		return $row;
	}

	/**
	* 토큰 발급
	*	 - 로그인 유지
	*	 - 유효시간(2시간)
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

	/** 토큰으로 회원정보 조회 */
	public function getByToken($token) {
		if (!isset($token) || !$token) {
			throw new Exception("토큰이 누락되었습니다.");
		}

		$sql = "SELECT * FROM member WHERE token = :token";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":token", $token);
		$result = $stmt->execute();
		if (!$result) {
			return false;
		}

		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$data) {
			throw new Exception("존재하지 않는 회원입니다.");
		}

		// 토큰 만료 체크
		$expireTime = strtotime($data['tokenExpires']);
		if ($expireTime < time()) {
			throw new Exception("토큰이 만료 되었습니다.");
		}

		unset($data['memPw']);

		return $data;
	}
}
