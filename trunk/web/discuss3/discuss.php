<?php
ob_start();
        require_once("discuss_func.inc.php");
$parm="";
if(isset($_GET['pid'])){
	$pid=intval($_GET['pid']);
	$parm="pid=".$pid;
}else{
	$pid=0;
}
if(isset($_GET['cid'])){
	$cid=intval($_GET['cid']);
	$parm.="&cid=".$cid;
}else{
	$cid=0;
}
       $prob_exist = problem_exist($pid, $cid);
                require_once("oj-header.php");
        echo "<title>HUST Online Judge WebBoard</title>";
?>

<center>
<div style="width:90%">
<?php
if ($prob_exist){?>
<div style="text-align:left;font-size:80%">
[ <a href="newpost.php<?php if ($pid!=0 && $cid!=null) echo "?pid=".$pid."&cid=".$cid;
else if ($pid!=0) echo "?pid=".$pid;
else if ($cid!=0) echo "?cid=".$cid;?>
">New Thread</a> ]</div>
<div style="float:left;text-align:left;font-size:80%">
Location :
<?php if ($cid!=null) echo "<a href=\"discuss.php?cid=".$cid."\">Contest ".$cid."</a>"; else echo "<a href=\"discuss.php\">MainBoard</a>";
if ($pid!=null && $pid!=0){
        $query="?pid=$pid";
        if($cid!=0) $query.="&cid=$cid";
         echo " >> <a href=\"discuss.php".$query."\">Problem ".$pid."</a>";

}
?>
</div>

<div style="float:right;font-size:80%;color:red;font-weight:bold">
<?php if ($pid!=null && $pid!=0 && ($cid=='' || $cid==null)){?>
<a href="../problem.php?id=<?php echo $pid?>">See the problem</a>
<?php }?>
</div>
<?php }
$sql = "SELECT `tid`, `title`, `top_level`, `topic`.`status`, `cid`, `pid`, CONVERT(MIN(`reply`.`time`),DATE) `posttime`, MAX(`reply`.`time`) `lastupdate`, `topic`.`author_id`, COUNT(`rid`) `count` FROM `topic`, `reply` WHERE `topic`.`status`!=2 AND `reply`.`status`!=2 AND `tid` = `topic_id`";
if (array_key_exists("cid",$_REQUEST)&&$_REQUEST['cid']!='') $sql.= " AND ( `cid` = '".mysqli_escape_string($mysqli,$_REQUEST['cid'])."'";
else $sql.=" AND ( ISNULL(`cid`)";
$sql.=" OR `top_level` = 3 )";
if (array_key_exists("pid",$_REQUEST)&&$_REQUEST['pid']!=''){
  $sql.=" AND ( `pid` = '".mysqli_escape_string($mysqli,$_REQUEST['pid'])."' OR `top_level` >= 2 )";
  $level="";
}
else
  $level=" - ( `top_level` = 1 AND `pid` != 0 )";
$sql.=" GROUP BY `topic_id` ORDER BY `top_level`$level DESC, MAX(`reply`.`time`) DESC";
$sql.=" LIMIT 30";
//echo $sql;
$result = pdo_query($sql);
$rows_cnt = count($result);
$cnt=0;
$isadmin = isset($_SESSION['administrator']);
?>
<table style="clear:both; width:100%">
<tr align=center class='toprow'>
        <td width="2%"><?php if ($isadmin) echo "<input type=checkbox>"; ?></td>
        <td width="3%"></td>
        <td width="4%">Prob</td>
        <td width="12%">Author</td>
        <td width="46%">Title</td>
        <td width="8%">Post Date</td>
        <td width="16%">Last Reply</td>
        <td width="3%">Re</td>
</tr>
<?php if ($rows_cnt==0) echo("<tr class=\"evenrow\"><td colspan=4></td><td style=\"text-align:center\">No thread here.</td></tr>");

for ($i=0;$i<$rows_cnt;$i++){
        mysqli_data_seek($result,$i);
         $row=$result[0];
        if ($cnt) echo "<tr align=center class='oddrow'>";
        else echo "<tr align=center class='evenrow'>";
        $cnt=1-$cnt;
        if ($isadmin) echo "<td><input type=checkbox></td>"; else echo("<td></td>");
        echo "<td>";
                if ($row->top_level!=0){
                        if ($row->top_level!=1||$row->pid==($pid==''?0:$pid))
                        echo"<b class=\"Top{$row->top_level}\">Top</b>";
                }
                else if ($row->status==1) echo"<b class=\"Lock\">Lock</b>";
                else if ($row->count>20) echo"<b class=\"Hot\">Hot</b>";
        echo "</td>";
        echo "<td>";
        if ($row->pid!=0) {
		if($row->cid)	
			echo "<a href=\"discuss.php?pid={$row->pid}"."&cid={$row->cid}\">";
		else
			echo "<a href=\"discuss.php?pid={$row->pid}\">";
		echo "{$row->pid}</a>";
        }
	echo "</td>";
        echo "<td><a href=\"../userinfo.php?user={$row->author_id}\">{$row->author_id}</a></td>";
        if($row->cid)echo "<td><a href=\"thread.php?tid={$row->tid}&cid={$row->cid}\">".htmlentities($row['title'],ENT_QUOTES,"UTF-8")."</a></td>";
        else echo "<td><a href=\"thread.php?tid={$row->tid}\">".htmlentities($row['title'],ENT_QUOTES,"UTF-8")."</a></td>";
        echo "<td>{$row->posttime}</td>";
        echo "<td>{$row->lastupdate}</td>";
        echo "<td>".($row->count-1)."</td>";
        echo "</tr>";
}


?>

</table>
</div>
</center>
<?php require_once("../template/$OJ_TEMPLATE/discuss.php")?>

