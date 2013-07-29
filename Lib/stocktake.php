<?php

//require_once("../Utils/output.php");

class stocktake {
	
	protected $db;

	/**
	 * METHODS:
	 * viewsStockTakeList
	 * addStockTake
	 * viewStockTakeStocks
	 * scanStock
	 * addCount
	 * undoCount
	 */
	
	public function __construct($db){
		
		$this->db = $db;
			
	}//end construct
	
	public function viewsStockTakeList(){
		$jimstocktake["sql"] = "
			SELECT ST.SessionNo, ST.SessionDate, ST.LocationNo, L.LocCode
			FROM JimStockTake ST
			LEFT JOIN JimStockLocation L
			ON ST.LocationNo = L.LocNo
			WHERE Status NOT IN ('CANCEL', 'FINISH')";
		
		$jimstocklocation["sql"] = "
			SELECT *
			FROM JimStockLocation";
		
		$data = array();
		
		$data["rs_stocktake"] = $this->db->select( $jimstocktake["sql"] );
		$data["rs_location"] = $this->db->select( $jimstocklocation["sql"] );
		
		return $data;
		
	}//end view
	
	public function addStockTake($data){
		extract($data);
		
		$insert_jimstocktake["sql"] = "
				INSERT INTO JimStockTake (
					SessionNo,
					SessionDate,
					LocationNo,
					Status,
					StockGroupsNo,
					BlindStockTake,
					StockCode,
					Notes,
					GLGroupNo,
					Zone,
					Row,
					Bay,
					Level,
					WHBinID,
					FloorStockOnly
				)  Values ( ?, ?, ?, 'Booked', NULL, 'F', '', '', NULL, NULL, NULL, NULL, NULL, NULL, 'F' )";
		
		$delete_jimstocktakecomm["sql"] = "
				DELETE
				FROM JimStockTakeComm
				WHERE SessionNo= ? ";
		
		$insert_jimstocktakecomm["sql"] = "
				INSERT INTO JimStockTakeComm (
					SessionNo,
					LineNum,
					Status,
					Comment,
					StatusTime,
					AddInit,
					AddDate,
					ModifyInit,
					ModifyDate
				) VALUES ( ?, 1, 'Ordered', '', NULL, ?, ?, ?, ? )";
		
		$update_jimstocktakemast["sql"] = "
				UPDATE JimStockTakeMast
				SET AvgCost = C.AvgCost
				FROM JimStockTakeMast M
				LEFT JOIN JimStockCost C ON M.StockNo = C.StockNo
				WHERE M.SessionNo = ? ";
		
		$date = (string) $date;
		$initials = (string) $initials;
		
		$insert_jimstocktake["params"] = array( &$sessNo, &$date, &$locNo );
		$delete_jimstocktakecomm["params"] = array( &$sessNo );
		$insert_jimstocktakecomm["params"] = array( &$sessNo, &$initials, &$date, &$initials, &$date );
		$update_jimstocktakemast["params"] = array( &$sessNo );
		
		$this->db->begin_transaction();
		
		$this->db->insert( $insert_jimstocktake["sql"], $insert_jimstocktake["params"] );
		$this->db->delete( $delete_jimstocktakecomm["sql"], $delete_jimstocktakecomm["params"] );
		$this->db->insert( $insert_jimstocktakecomm["sql"], $insert_jimstocktakecomm["params"] );
		$this->db->update( $update_jimstocktakemast["sql"], $update_jimstocktakemast["params"] );
		
		$message = $this->db->commit();
		
		return $message;
		
	}//end addStockTake
	
	public function viewStockTakeStocks($id){
		$sessid = $id->ret_val();
		
		$jimstocktakemast["sql"] = "
			SELECT  M.*,  D.QtyCounted,  D.Unit D_Unit,  D.UnitQty D_UnitQty,  D.UserNo D_UserNo,  D.LineID,  JS.StockCode,  JS.StockDesc,  JS.QtyDecPlaces,
			C.UsrInitials as UserInit,  C1.UsrInitials as D_UserInit,  WHB.WHFQBin,
				( SELECT SUM(T.Qty)
				FROM JimStockTran T
				WHERE T.Type = 'I'
				AND T.StockNo = M.StockNo
				AND T.QtyB = 0
				AND T.Location = 13) QtyLineRes,
				( SELECT sq.SerialNo
				FROM JimStock sq
				WHERE sq.StockNo = M.StockNo
				AND (sq.SerialNo = 'N' OR sq.SerialNo = 'P')
				) SerialNo
			FROM JimStockTakeMast M
			LEFT JOIN JimStockTakeDet D
				ON M.RowID = D.RowID
			LEFT JOIN JimStock JS
				ON M.StockNo = JS.StockNo
			LEFT JOIN JimCardFile C1
				ON D.UserNo = C1.CardNo
			LEFT JOIN JimCardFile C
				ON M.UserNo = C.CardNo
			LEFT JOIN JimWHBin WHB
				ON M.WHBinID = WHB.WHBinID
			WHERE M.SessionNo = ?
			ORDER BY JS.StockCode, D.LineID ";
		
		$jimstocktakecomm["sql"] = "
			SELECT *
			FROM JimStockTakeComm
			WHERE SessionNo = ?
			ORDER BY LineNum";
		
		$jimstock["sql"] = "
			SELECT Distinct  JS.StockCode
			FROM JimStock JS LEFT JOIN JimStockQtyView_IL JSQ ON JS.StockNo = JSQ.StockNo  
			AND JSQ.LocNo = 13
			WHERE JS.StockNo > -1  
			AND JS.SerialNo IN ('N', 'P')";
		
		$jimstocktake["sql"] = "
			SELECT *
			FROM JimStockTake JS
			LEFT JOIN JimStockLocation L
			ON JS.LocationNo = L.LocNo
			WHERE JS.SessionNo = ? ";
		
		$jimstocktakedet["sql"] = "
			SELECT *
			FROM JimStockTakeDet D
			LEFT JOIN JimStockTakeMast M
			ON D.RowID = M.RowID
			LEFT JOIN JimCardFile C
			ON M.UserNo = C.CardNo
			WHERE M.SessionNo = ? ";
		
		$jimstocktakedet["params"] = $jimstocktake["params"] = $jimstocktakecomm["params"] = $jimstocktakemast["params"] = array( &$sessid );
		
		$result = array();
		$stocktake_count = count( $this->db->select( $jimstocktakemast["sql"], $jimstocktakemast["params"] ) );
		
		$result["rs_stocktake"] = ( $stocktake_count ) ? cleanList( $this->db->select( $jimstocktakemast["sql"], $jimstocktakemast["params"] ) ) : NULL;
		$result["rs_stocktakecomm"] = $this->db->select( $jimstocktakecomm["sql"], $jimstocktakecomm["params"] );
		$result["rs_stock"] = json_encode( $this->db->select( $jimstock["sql"] ) );
		$result["rs_stockinfo"] = $this->db->select( $jimstocktake["sql"], $jimstocktake["params"] );
		$result["rs_stockdet"] = $this->db->select( $jimstocktakedet["sql"], $jimstocktakedet["params"] );
		
		return $result;
		
	}//end viewStock
	
	public function scanStock($data){
		extract($data);
		$result = array();
		
		$stockID = $stockid->ret_val();
		$stockCode = (string) $stockcode->ret_val();
		
		$indcount["sql"] = "
			SELECT *
			FROM JimStockTakeMast M
			LEFT JOIN JimStockTakeDet D
			ON M.RowID = D.RowID
			LEFT JOIN JimStock JS
			ON M.StockNo = JS.StockNo
			LEFT JOIN JimCardFile C
			ON M.UserNo = C.CardNo
			WHERE M.SessionNo = ?
			AND (
				JS.StockCode = ? OR
				JS.BarCode = ?
			)";
		
		$jimstocktakemast["sql"] = "
			SELECT  M.*,  D.QtyCounted,  D.Unit D_Unit,  D.UnitQty D_UnitQty,  D.UserNo D_UserNo,  D.LineID,  JS.StockCode,  JS.StockDesc,  JS.QtyDecPlaces,
			C.UsrInitials as UserInit,  C1.UsrInitials as D_UserInit,  WHB.WHFQBin,
				( SELECT SUM(T.Qty)
				FROM JimStockTran T
				WHERE T.Type = 'I'
				AND T.StockNo = M.StockNo
				AND T.QtyB = 0
				AND T.Location = 13) QtyLineRes,
				( SELECT sq.SerialNo
				FROM JimStock sq
				WHERE sq.StockNo = M.StockNo
				AND (sq.SerialNo = 'N' OR sq.SerialNo = 'P')
				) SerialNo
			FROM JimStockTakeMast M
			LEFT JOIN JimStockTakeDet D
				ON M.RowID = D.RowID
			LEFT JOIN JimStock JS
				ON M.StockNo = JS.StockNo
			LEFT JOIN JimCardFile C1
				ON D.UserNo = C1.CardNo
			LEFT JOIN JimCardFile C
				ON M.UserNo = C.CardNo
			LEFT JOIN JimWHBin WHB
				ON M.WHBinID = WHB.WHBinID
			WHERE M.SessionNo = ?
			AND (
				JS.StockCode = ? OR
				JS.BarCode = ?
			)
			ORDER BY JS.StockCode, D.LineID ";
		
		$jimstock["sql"] = "
			SELECT StockNo, StockCode, StockDesc, SerialNo, BarCode
			FROM JimStock
			WHERE SerialNo IN ('N', 'P')
			AND (
				StockCode = ? OR (
					BarCode = ? AND
					BarCode <> ''
				)
			)";
		
		$jimstocktakemast["params"] = $indcount["params"] = array( &$stockID, &$stockCode, &$stockCode );
		$jimstock["params"] = array( &$stockCode, &$stockCode );
		
		$result["rs_indcount"] = $this->db->select( $indcount["sql"], $indcount["params"] );
		
		// If a stock exists in a stocktake session get data
		$result["rs_jimstock"] = ( $result["rs_indcount"] ) ? 
										cleanList( $this->db->select( $jimstocktakemast["sql"], $jimstocktakemast["params"] ) ) : 
										$this->db->select( $jimstock["sql"], $jimstock["params"] ) ;
		
		// If a stock does not exist
		if ( !$result["rs_indcount"] ){
		
			// If no result found set stock to null
			if( !$result["rs_jimstock"] ){
				$result["rs_jimstock"] = null;
				return $result;
			}
			
			$jimstocklocation["sql"] = "
				SELECT ST.SessionNo, ST.SessionDate, ST.LocationNo, L.LocCode
				FROM JimStockTake ST
				LEFT JOIN JimStockLocation L
				ON ST.LocationNo = L.LocNo
				WHERE Status NOT IN ('CANCEL', 'FINISH')
				AND ST.SessionNo = ? ";
			
			$jimstocklocation["params"] = array( &$stockID );
			
			$rs_jimstock = $this->db->select( $jimstock["sql"], $jimstock["params"] ) ;
			$rs_jimstocklocation = $this->db->select( $jimstocklocation["sql"], $jimstocklocation["params"] );
			
			$jimstocktran["sql"] = "
				SELECT SUM(QtyB) QtyB
				FROM JimStockTran
				WHERE ( ( Type in ('P', 'R' ) AND PackId is null )
				OR ( Type = 'G' ) )
				AND QtyB > 0
				AND StockNo = ?
				AND Location = ?
				AND WHBinID is NULL ";
			
			$rs_stockno = $rs_jimstock[0]["StockNo"];
			$rs_locationno = $rs_jimstocklocation[0]["LocationNo"];
			
			$jimstocktran["params"] = array( &$rs_stockno, &$rs_locationno );
			
			$rs_jimstocktran = $this->db->select( $jimstocktran["sql"], $jimstocktran["params"]);
			
			// Set the right QOH and AdjustQty of stock based on location
			$arr = array(
					"QtyOnHand" => ( $rs_jimstocktran[0]["QtyB"] ) ? $rs_jimstocktran[0]["QtyB"] : 0,
					"AdjustQty" => - ( ( $rs_jimstocktran[0]["QtyB"] ) ? $rs_jimstocktran[0]["QtyB"] : 0 ),
					"RowID" => "");
			
			$result["rs_jimstock"][0] += $arr;
		}
		
		return $result;
		
	}//end scanStock
	
	public function addCount($data){
		
		extract($data);
		$this->db->begin_transaction();
		
		$undo = array();
		$sessid = $id->ret_val();
		$stockNo = $stockno->ret_val();
		$rowID = $row_id->ret_val();
		$unitQty = $unit_qty->ret_val();
		$cardNo = $card_no->ret_val();
		$adjQty = $adj_qty->ret_val();
		$qtyCounted = $qty_counted->ret_val();
		$userNo = $user_no->ret_val();
		
		$checker["sql"] = "
				SELECT *
				FROM JimStockTakeMast
				WHERE SessionNo = ?
				AND StockNo = ? ";
		
		$checker["params"] = array( &$sessid, &$stockNo );
		
		$rs_checker = $this->db->select( $checker["sql"], $checker["params"] );
		
		// If stock already exists in a stocktake
		If ( $rs_checker ) {
			
			$jstd["sql"] = "
				SELECT MAX(LineID)
				FROM JimStockTakeDet T
				LEFT JOIN JimStockTakeMast M
				ON T.RowID = M.RowID
				WHERE T.RowID = ?
				AND M.SessionNo = ? ";
			
			$jstd["params"] = array( &$rowID, &$sessid );
			
			$rs_stocktakedet = $this->db->select( $jstd["sql"], $jstd["params"] );
				
			foreach($rs_stocktakedet[0] as $key => $value) $lineID = $value;
			$lineID++;
				
			$insert_jstd["sql"] = "
				INSERT INTO JimStockTakeDet (
					RowID,
					LineID,
					QtyCounted,
					Unit,
					UnitQty,
					UserNo
				)  Values( ?, ?, ?, 'UNIT', 1, ?)";
			
			$update_jstm["sql"] = "
				UPDATE JimStockTakeMast
				SET AdjustQty = ?
				WHERE RowID = ? ";
			
			$insert_jstd["params"] = array( &$rowID, &$lineID, &$unitQty, &$cardNo );
			$update_jstm["params"] = array( &$adjQty, &$rowID );
			
			$val = $this->db->insert( $insert_jstd["sql"], $insert_jstd["params"] );
			$this->db->update( $update_jstm["sql"], $update_jstm["params"] );
			
			$undo["RowID"] = $rowID;
			$undo["LineID"] = $lineID;
			$undo["NewStock"] = FALSE;
			
		}
		// Create new stocktake product for the stock
		else
		{
			
			$insert_jstm["sql"] = "
				INSERT INTO JimStockTakeMast (
					SessionNo,
					StockNo,
					QtyOnHand,
					AdjustQty,
					Unit,
					UnitQty,
					UserNo,
					WHBinID,
					State,
					Confirmed
				)  Values ( ?, ?, ?, ?, 'UNIT', ?, ?, NULL, 2, 'F')";
			
			$insert_jstm["params"] = array( &$sessid, &$stockNo, &$qtyCounted, &$adjQty, &$unitQty, &$userNo );
			$lastRowID = $this->db->insert( $insert_jstm["sql"], $insert_jstm["params"] );
			
			$jstm["sql"] = "
					SELECT MAX(RowID)
					FROM JimStockTakeMast";
	
			$rs_maxRowID = $this->db->select( $jstm["sql"] );
			foreach($rs_maxRowID[0] as $key => $value) $row_id = $value;
	
			$max_jstd["sql"] = "
				SELECT MAX(LineID)
				FROM JimStockTakeDet T
				LEFT JOIN JimStockTakeMast M
				ON T.RowID = M.RowID
				WHERE T.RowID = ?
				AND M.SessionNo = ? ";
			
			$max_jstd["params"] = array( &$rowID, &$sessid );
			
			$rs_stocktakedet = $this->db->select( $max_jstd["sql"], $max_jstd["params"] );
			
			foreach($rs_stocktakedet[0] as $key => $value) $lineID = $value;
			(!$lineID) ? $lineID = 1 : NULL;
			
			$insert_jstd2["sql"] = "
				INSERT INTO JimStockTakeDet (
					RowID,
					LineID,
					QtyCounted,
					Unit,
					UnitQty,
					UserNo
				)  Values(
					".$row_id.",
					".$lineID.",
					".$unit_qty->ret_val().",
					'UNIT',
					1,
					".$card_no->ret_val().");
				SELECT SCOPE_IDENTITY() AS IDENTITY_COLUMN_NAME";
			
			$insert_jstd2["params"] = array( &$rowID, &$lineID, &$unitQty, &$cardNo );
			
			$val = $this->db->insert( $insert_jstd2["sql"], $insert_jstd2["params"] );
			
			$undo["RowID"] = $row_id;
			$undo["LineID"] = $lineID;
			$undo["NewStock"] = TRUE;
			
		}

		$message = $this->db->commit();
		$undo["message"] = $message;
		
		return $undo;
		
	}//end addCount
	
	public function undoCount($params){
		extract($params);
		
		$rowID = $row_id->ret_val();
		$lineID = $line_id->ret_val();
		
		$jstd["sql"] = "
				SELECT QtyCounted
				FROM JimStockTakeDet
				WHERE RowID = ?
				AND LineID = ? ";
		
		$delete_jstd["sql"] = "
				DELETE FROM JimStockTakeDet
				WHERE RowID = ?
				AND LineID = ? ";
		
		$delete_jstd["params"] = $jstd["params"] = array( &$rowID, &$lineID );
		
		$this->db->begin_transaction();
		
		$rs_unitqty = $this->db->select( $jstd["sql"], $jstd["params"] );
		$this->db->delete( $delete_jstd["sql"], $delete_jstd["params"] );
		
		// If stock count is only 1
		if ( $line_id->ret_val() == 1 ) {
			
			$delete_jstm["sql"] = "
					DELETE FROM JimStockTakeMast
					WHERE RowID = ".$row_id->ret_val();
			
			$delete_jstm["params"] = array( &$rowID );
			
			$this->db->delete( $delete_jstm["sql"], $delete_jstm["params"]);
			
		} else {
			$unitQty = $rs_unitqty[0]["QtyCounted"];
			
			$update_jstm["sql"] = "
				UPDATE JimStockTakeMast
				SET AdjustQty = ( AdjustQty - ? )
				WHERE RowID = ? ";
			
			$update_jstm["params"] = array( &$unitQty, &$rowID );
			
			$this->db->update( $update_jstm["sql"], $update_jstm["params"] );
		}
		
		$message = $this->db->commit();
		return $message;
		
	}//end undoCount
	
}//end class