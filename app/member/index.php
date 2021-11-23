<?php
include_once "../common.php";

/**
* 회원가입, 로그인 처리 
*
*/
$member = Member::getInstance(); // Member 인스턴스
try {
	switch (Request::get("mode")) {
		case "join": // 회원가입 처리 
			$memberInfo = $member->join($in);
			if ($memberInfo) {
				$success = true;
				$returnData = $memberInfo;
			} else {
				throw new Exception("회원가입 실패");
			}
			break;
		case "update" :  // 회원정보 수정
			$result = $member->update($in);
			if (!$result) {
				throw new Exception("회원정보 수정 실패하였습니다.");
			}
			$success = true;
			$message = "회원정보가 수정되었습니다.";
			break;
		case "login" : // 로그인 처리 
			$token = $member->login($in);
			if (!$token) {
				throw new Exception("로그인 실패하였습니다.");
			}
			$success = true;
			$returnData = ["token" => $token];
			break;
		/** 토큰으로 회원 정보 조회 */
		case "get_member" : 
			$memberData = $member->getByToken($in['token']);
			if (!$memberData) {
				throw new Exception("토큰 회원조회 실패");
			}
			
			$success = true;
			$returnData = $memberData;
			break;
		default : 
			if (Request::get("origin") != 'front') {
				echo "<script>location.replace('/');</script>";
				exit;
			}
	}
} catch(Exception $e) {
	$message = $e->getMessage() . "[".$e->getFile()."/".$e->getLine()."]";
}

include_once "../output.php";