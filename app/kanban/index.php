<?php
include_once "../common.php"; // 공통 정의 부분
/** 
* 작업 추가, 수정, 삭제 처리 
*
*/
$kanban = Kanban::getInstance();

try {
	/** 회원 전용 서비스 체크 */
	if (!Request::get("memNo")) {
		throw new Exception("회원전용 서비스 입니다.");
	}
	
	switch(Request::get("mode")) {
		/** 작업 추가 */
		case "add" : 
			$idx = $kanban->addWork($in);
			if (!$idx) {
				throw new Exception("작업등록 실패하였습니다.");
			}
			
			$success = true;
			$returnData = ["idx" => $idx];
			break;
		/** 작업 수정 */
		case "edit" : 
			$result = $kanban->editWork($in);
			if (!$result) {
				throw new Exception("작업수정 실패하였습니다.");
			}
			
			$success = true;
			$returnData = $result;
			break;
		/** 작업 삭제 */
		case "delete" : 
			if (!isset($in['idx']) || !$in['idx']) {
					throw new Exception("작업등록번호 누락");
			}
			
			$info = $kanban->get($in['idx']);
			if (!$info) {
				throw new Exception("삭제할 작업내역이 없습니다.");
			}
			
			if ($info['memNo'] != $in['memNo']) {
				throw new Exception("본인이 작성한 작업내역만 삭제 가능합니다.");
			}
			
			$result = $kanban->deleteWork(Request::get("idx"));
			if (!$result) {
				throw new Exception("작업삭제 실패하였습니다.");
			}
			
			$success = true;
			break;
		/** 작업 목록 */
		case "getList" : 
			$memNo = Request::get("memNo", 0);
			$status = Request::get("status", "ready");
			$result = $kanban->getList($memNo, $status);
			if (!$result) {
				throw new Exception("작업 목록 조회 실패");
			}
			
			$success = true;
			$returnData = $result;
			break;
		/** 작업 내용 */
		case "get" : 
			$idx = Request::get("idx");
			if (!$idx) {
				throw new Exception("작업등록번호 누락");
			}
			
			$result = $kanban->get($idx);
			if (!$result) {
				throw new Exception("작업내역이 없습니다.");
			}
			
			$success = true;
			$returnData = $result;
			break;
		default :
			if (Request::get("origin") != 'front') {
				echo "<script>location.replace('/');</script>";
				exit;
			}
	}
} catch (Exception $e) {
	$message = $e->getMessage();
}

include_once "../output.php";