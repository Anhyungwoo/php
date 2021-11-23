<?php 
/**
* 작업 추가, 수정, 삭제, 조회 
*
*/
class Kanban {
	private $db;
	private static $instance;
	
	/** 필수 입력항목 */
	private $required = [
		'memNo' => "회원만 사용가능한 서비스 입니다.",
		'status' => "작업 구분을 선택하세요.",
		'subject' => "작업명을 입력하세요.",
		'content' => "작업내용을 입력하세요.",
	];
	
	private function __construct() {
		$this->db = DB::getInstance();
	}
	
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new Kanban();
		}
		
		return self::$instance;
	}
	
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
	
	/** 작업 수정 */
	public function editWork($data) {
		$this->required['idx'] = "작업등록번호가 누락되었습니다.";
		$this->checkData($data);
		
		$info = $this->get($data['idx']);
		if (!$info) {
			throw new Exception("수정할 작업내역이 없습니다.");
		}
		
		if ($info['memNo'] != $data['memNo']) {
			throw new Exception("본인이 작성한 작업 내역만 수정 가능합니다.");
		}
		
		$sql = "UPDATE worklist 
						SET 
							status = :status,
							subject = :subject,
							content = :content 
					WHERE 
						idx = :idx";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":status", $data['status']);
		$stmt->bindValue(":subject", $data['subject']);
		$stmt->bindValue(":content", $data['content']);
		$stmt->bindValue(":idx", $data['idx'], PDO::PARAM_INT);
		
		$result = $stmt->execute();
		
		return $result;
	}
	
	/** 작업 삭제 */
	public function deleteWork($idx) {
		$sql = "DELETE FROM worklist WHERE idx = :idx";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":idx", $idx, PDO::PARAM_INT);
		return $stmt->execute();
	}
	
	
	/**
	* 작업목록(회원이 작성한)
	*
	* @param $memNo 회원번호
	* @param $status - ready, progress, done 
	*/
	public function getList($memNo, $status = 'ready') {
		$sql = "SELECT a.*, m.memId, m.memNm FROM worklist a 
						LEFT JOIN member m ON a.memNo = m.memNo 
					WHERE a.memNo = :memNo AND a.status = :status 
					ORDER BY a.regDt DESC";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":memNo", $memNo, PDO::PARAM_INT);
		$stmt->bindValue(":status", $status);
		$result = $stmt->execute();
		
		$rows = [];
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			//$rows[] = $row;
			array_push($rows, $row);
		}
		
		return $rows;
	}
	
	/**
	* 작업내용 조회 
	*
	* @param $idx 작업 등록번호 
	*/
	public function get($idx) {
		$sql = "SELECT * FROM worklist WHERE idx = :idx";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(":idx", $idx, PDO::PARAM_INT);
		$result = $stmt->execute();
	
		if (!$result) {
			return false;
		}
		
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($data) {
			$data['regDt'] = date("Y.m.d", strtotime($data['regDt']));
			$data['contentHtml'] = nl2br($data['content']);
		}
		
		return $data;
	}
	
	/** 데이터 유효성 검사 */
	public function checkData($data) {
		if ($data['mode'] == 'edit') {
			$this->required['idx'] = "작업등록번호가 누락되었습니다.";
		}
		
		foreach ($this->required as $key => $msg) {
			if (!isset($data[$key]) || ($data[$key] && trim($data[$key]) == "")) {
				throw new Exception($msg);
			}
		}
	}
}