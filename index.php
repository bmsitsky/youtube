<?php
error_reporting(0);
//header('Content-type: text/plain; charset=utf-8');
function dlPage($href) {

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
  /*  curl_setopt($curl, CURLOPT_HTTPHEADER , array(
     'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
));*/
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_URL, $href);
    curl_setopt($curl, CURLOPT_REFERER, $href);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.125 Safari/533.4");
    $str = curl_exec($curl);
    curl_close($curl);

    return $str;
 }
include("config.php");

//$channelID  = 'PLtqS23VndQg9v1eRmNgFXVD0-Osys5ks7';
//$playlistID  = 'PLtqS23VndQg9v1eRmNgFXVD0-Osys5ks7';
$maxResults = 50;
//$videoList = json_decode(file_get_contents('https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&channelId='.$channelID.'&maxResults='.$maxResults.'&key='.$API_key.''));
// Khởi tạo CURL
    $get_channel = false;
    $ready = false;
    $banquyenok = false;
	$get_video = false;
    if(isset($_POST['search']))
    {
       // echo "okkkkkkkk";
        $ready = true;
        $link = $_POST['link'];
        if(strpos($link,"playlist"))
        {
             $get_channel = false;
              $arrlink = explode("=",$link);
              $playlistID= $arrlink [1];
             //echo "hh:".$channelID;
        }
		
		else  if(strpos($link,"watch"))
		{
			$get_video = true;
			$arrlink = explode("?v=",$link);
            $videoID = $arrlink [1];
		}
        else 
        {
            $get_channel = true;
            $arrlink = explode("/",$link);
             $channelID = $arrlink [4];
            // echo "mm:".$playlistID;
        }
          if($_POST['banquyen']=="ok")
                $banquyenok = true;
              else  $banquyenok = false;
        
    } else $ready = false;
    if($get_channel == true)
        $url_getx = 'https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&channelId='.$channelID.'&maxResults='.$maxResults.'&key='.$API_key.'';
	else if ($get_video == true)
		$url_getx = 'https://www.googleapis.com/youtube/v3/videos?id='.$videoID.'&key='.$API_key.'&part=snippet,contentDetails,statistics';
    else     $url_getx = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId='.$playlistID.'&maxResults='.$maxResults.'&key='.$API_key.'';
    //GET https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=PLtqS23VndQg9v1eRmNgFXVD0-Osys5ks7&key=[YOUR_API_KEY] HTTP/1.1
   
//echo $result;

$stt = 0;
$arrBanquyen = array();
$arrTitle = array();
$arrViews = array();
$arrVideoId = array();
$totalViews = 0;
$totalPage = 10;
$totalPage2 = -1;
$maxPage = 0;
$errorCode = 0;
$errorMes = 0;
$url_get = $url_getx;
if($get_channel == true && $ready == true)
{

while($maxPage<$totalPage)
{

 $ch = curl_init($url_get);
    // Thiết lập có return
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    $result = curl_exec($ch);
    curl_close($ch);

$videoList = json_decode($result);
$totalPage = $videoList->pageInfo->totalResults;
$totalPage2 = $totalPage ;
if($videoList->error->code=="403")
{
    $errorCode = "403";
    $errorMes = $videoList->error->message;
}
foreach($videoList->items as $item){
    $stt++;
    $maxPage = $maxPage + 1;
       if(isset($item->id->videoId)){

        $url_view = 'https://www.googleapis.com/youtube/v3/videos?part=statistics&id='.$item->id->videoId.'&key='.$API_key;
        $ch2 = curl_init($url_view);
        // Thiết lập có return
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $result = curl_exec($ch2);
        curl_close($ch2);
        $videoList2 = json_decode($result);
        $views = 0;
        foreach($videoList2->items as $item2)
       {
            $views = $item2->statistics->viewCount;
            $arrViews[] = $views;
            $totalViews = $totalViews + $views;
       }
			$title_video = $item->snippet->title;
			$arrTitle[] =  $title_video;
			$arrVideoId[] = $item->id->videoId;
        $banquyen = "";
        if($banquyenok == true)
        {
              $url_run2 = 'https://www.youtube.com/watch?v='.$item->id->videoId;
        
        $html = dlPage($url_run2);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' .$html);
      //  echo $html;
        $xpath = new DOMXPath($dom);
            $href = $xpath->query('//ul[@class="watch-extras-section"]//li[@class="watch-meta-item yt-uix-expander-body"]');
            foreach($href as $container) {
              // $data = $href->item($container);
                $title_pro = $container->nodeValue;
         
                if(strpos($title_pro,"Bên cấp phép"))
                {
                   $title_pro = str_replace("Bên cấp phép cho YouTube:"," ",$title_pro); 
                    $banquyen = $banquyen.$title_pro."<br> ";
                }
            }
           $arrBanquyen[] = $banquyen ;
      }
     
   }

 }
 $url_get = $url_getx."&pageToken=".$videoList->nextPageToken;
}
}

else if($get_video == true && $ready == true)
{
 $ch = curl_init($url_get);
    // Thiết lập có return
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    $result = curl_exec($ch);
    curl_close($ch);
	
	$videoList2 = json_decode($result);
        $views = 0;
        foreach($videoList2->items as $item2)
       {
            $views = $item2->statistics->viewCount;
            $arrViews[] = $views;
            $totalViews = $totalViews + $views;
			$title_video = $item2->snippet->title;
			$arrTitle[] =  $title_video;
			$arrVideoId[] = $item2->id;
       }
        // //div[@class="blogArticle"]
        
        
       // echo "hello:".$url_run2;
        $banquyen = "";
        if($banquyenok == true)
        {
              $url_run2 = 'https://www.youtube.com/watch?v='.$item2->id;
   
        $html = dlPage($url_run2);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' .$html);
      //  echo $html;
        $xpath = new DOMXPath($dom);
            $href = $xpath->query('//ul[@class="watch-extras-section"]//li[@class="watch-meta-item yt-uix-expander-body"]');
            foreach($href as $container) {
              // $data = $href->item($container);
                $title_pro = $container->nodeValue;
              
                if(strpos($title_pro,"Bên cấp phép"))
                {
                   $title_pro = str_replace("Bên cấp phép cho YouTube:"," ",$title_pro); 
                    $banquyen = $banquyen.$title_pro."<br> ";
                }
            }
           $arrBanquyen[] = $banquyen ;
		}
}

else if($ready == true)
{
  
    $totalPage = $totalPage;
    $totalPage2 = $totalPage ;
    $nextRun = true;
while($nextRun == true&&$maxPage<$totalPage)
{
 $ch = curl_init($url_get);
    // Thiết lập có return
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    $result = curl_exec($ch);
    curl_close($ch);
 
    $videoList = json_decode($result);
    $totalPage = $videoList->pageInfo->totalResults;
	
    if($videoList->error->code="403")
{
    $errorCode = "403";
    $errorMes = $videoList->error->message;
}
    foreach($videoList->items as $item){
     $stt++;
      $maxPage = $maxPage + 1;
       if(isset($item->snippet->resourceId->videoId)){
   
        $url_view = 'https://www.googleapis.com/youtube/v3/videos?part=statistics&id='.$item->snippet->resourceId->videoId.'&key='.$API_key;
        $ch2 = curl_init($url_view);
        // Thiết lập có return
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $result1 = curl_exec($ch2);
        curl_close($ch2);
        $videoList2 = json_decode($result1);
        $views = 0;
        foreach($videoList2->items as $item2)
       {
            $views = $item2->statistics->viewCount;
           
             $totalViews = $totalViews + $views;
       }
        $arrViews[] = $views;
        // //div[@class="blogArticle"]
        $title_video = $item->snippet->title;
           // echo   $title_video;
        $arrTitle[] =  $title_video;
        $arrVideoId[] = $item->snippet->resourceId->videoId;
        $banquyen = "";
        if($banquyenok == true)
        {
               $url_run2 = 'https://www.youtube.com/watch?v='.$item->snippet->resourceId->videoId;
     
        $html = dlPage($url_run2);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' .$html);
      //  echo $html;
        $xpath = new DOMXPath($dom);
             $href = $xpath->query('//ul[@class="watch-extras-section"]//li[@class="watch-meta-item yt-uix-expander-body"]');
            foreach($href as $container) {
              // $data = $href->item($container);
                $title_pro = $container->nodeValue;
                //echo $title_pro;
                if(strpos($title_pro,"Bên cấp phép"))
                {
                   $title_pro = str_replace("Bên cấp phép cho YouTube:"," ",$title_pro); 
                    $banquyen = $banquyen.$title_pro."<br> ";
                    
                }
            }
            $arrBanquyen[] = $banquyen ;
        }
     
   }
 } //foreach
 $url_get = $url_getx."&pageToken=".$videoList->nextPageToken;
 $nextRun = false;
 if($videoList->nextPageToken!="")
    $nextRun = true;
}
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Metadata Youtube</title>
    <!-- Bootstrap Styles-->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FontAwesome Styles-->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- Morris Chart Styles-->
    <link href="assets/js/morris/morris-0.4.3.min.css" rel="stylesheet" />
    <!-- Custom Styles-->
    <link href="assets/css/custom-styles.css" rel="stylesheet" />
    <!-- Google Fonts-->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    <link rel="stylesheet" href="assets/js/Lightweight-Chart/cssCharts.css"> 
    <script src="https://apis.google.com/js/api.js"></script>
    <script>
  /**
   * Sample JavaScript code for youtube.playlistItems.insert
   * See instructions for running APIs Explorer code samples locally:
   * https://developers.google.com/explorer-help/guides/code_samples#javascript
   */

  function authenticate() {
    return gapi.auth2.getAuthInstance()
        .signIn({scope: "https://www.googleapis.com/auth/youtube.force-ssl"})
        .then(function() { console.log("Sign-in successful"); },
              function(err) { console.error("Error signing in", err); });
  }
  function loadClient() {
    gapi.client.setApiKey("<?php echo $API_key;?>");
    return gapi.client.load("https://www.googleapis.com/discovery/v1/apis/youtube/v3/rest")
        .then(function() { console.log("GAPI client loaded for API"); },
              function(err) { console.error("Error loading GAPI client for API", err); });
  }
  // Make sure the client is loaded and sign-in is complete before calling this method.
  function execute(videoId) {
    return gapi.client.youtube.playlistItems.insert({
      "part": "snippet",
      "resource": {
        "snippet": {
          "playlistId": "<?php echo $playlist?>",
          "resourceId": {
            "kind": "youtube#video",
            "videoId": videoId
          }
        }
      }
    })
        .then(function(response) {
                // Handle the results here (response.result has the parsed body).
                if(response.status == "200")
                    alert("Thêm thành công");
                else  alert("Có lỗi khi thêm vào play list");
                console.log("Response", response);
              },
              function(err) { console.error("Execute error", err); });
  }

  gapi.load("client:auth2", function() {
    gapi.auth2.init({client_id: "<?php echo $clientId?>"});
  });
</script>

</head>

<body>
    <div id="wrapper">
        <nav class="navbar navbar-default top-navbar" role="navigation">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php"><strong>Youtuber Tool</strong></a>
            </div>

            <ul class="nav navbar-top-links navbar-right">
              
                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
                        <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
        </nav>
        <!--/. NAV TOP  -->
        <nav class="navbar-default navbar-side" role="navigation">
        <div id="sideNav" href=""><i class="fa fa-caret-right"></i></div>
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">

                    <li>
                        <a class="active-menu" href="index.php"><i class="fa fa-dashboard"></i> Bảng điều khiển</a>
                    </li>
                    <li>
                        <a href="index.php"><i class="fa fa-desktop"></i>List video</a>
                    </li>
                </ul>

            </div>

        </nav>
        <!-- /. NAV SIDE  -->
      
        <div id="page-wrapper">
          <div class="header"> 
                        <h1 class="page-header">
                            Hệ thống <small>Metadata Youtube</small>
                        </h1>
                        <ol class="breadcrumb">
                      <li><a href="#">Home</a></li>
                      <li class="active">List video</li>
                    </ol> 
                                    
        </div>
            <div id="page-inner">
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                 <form name="frmSearch" action="" method="post">           
                <div class="form-group col-lg-9">
                                            <label>Nhập link Channel hoặc Playlist</label>
                                            <input name="link" id="link" class="form-control" placeholder="Nhập link">
                                        </div>

                          <div class="form-group col-lg-3">
                            <br>
               
                                <button type="submit" name="search" value="s" class="btn btn-primary" id="searchindex">
                                    <span class="glyphicon glyphicon-search" aria-hidden="true"></span> Check
                                </button>
                                 <input type="checkbox" class="form-check-input" id ="exampleCheck1" name="banquyen" value="ok">
                                 <label class="form-check-label" for="exampleCheck1">Bản quyền </label>
                            </form>
                        </div>
                </div>
               
                <div class="col-md-12 col-sm-12 col-xs-12">
                      <div class="form-group col-lg-6">
                        <label> Tổng: <?php echo $totalViews ?> lượt xem</label>
                            <button class="btn btn-primary" onclick="authenticate().then(loadClient)">Authorize Load Token</button>
                        </div>
                        <div class="form-group col-lg6">
                                <button class="btn btn-primary" id="addPlay">Add vào Playlist</button>
                        </div>
                </div>
               
                </div>

                <div class="row">
               
                    <div class="col-md-12 col-sm-12 col-xs-12">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Danh sách video
                            </div> 
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                  <th>Video Id</th>
                                                <th>Tiêu đề video</th>
                                                <th>Tổng số Views</th>
                                                 <th>Bản quyền</th>
                                                <th>Thêm vào List</th>
                                             </tr>
                                        </thead>
                                        <tbody>
                                            <?php
     
                                                $stt = 0;
                                                //if($_POST['search']=="s"&&$errorCode == "403")
                                                //     echo '<tr><td colspan="6" class="text-center"><h4><span class="label label-danger">'.$errorMes.'</span></h4></td></tr>';
                                            //else
                                            if($_POST['search']=="s"&&count($arrTitle) == 0)
                                            {
                                                 echo '<tr><td colspan="6" class="text-center"><h4><span class="label label-warning">Link Channel hoặc playlist không đúng</span></h4></td></tr>';
                                            }
                                            for($i=0;$i<count($arrTitle);$i++) { 
                                                $stt++;
                                                $j= $i+1;
                                                echo '<tr>
                                                 <td>'.$stt.'</td>
                                                  <td><a href="https://www.youtube.com/watch?v='.$arrVideoId[$i].'" target="_blank">'.$arrVideoId[$i].'</a></td>
                                                <td>'.$arrTitle[$i].'</td>
                                                <td>'.$arrViews[$i].'</td>
                                                 <td>'.$arrBanquyen[$i].'</td>
                                                <td><button value='.$arrVideoId[$i].' class="addList">Add</button></td>
                                            </tr>';
                                              }
                                            ?>
                                            
                                           
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- /. ROW  -->
                <footer><p>2020 @ Code by tuanxt</p>
                </footer>
            </div>
            <!-- /. PAGE INNER  -->
        </div>
        <!-- /. PAGE WRAPPER  -->
    </div>
    <!-- /. WRAPPER  -->
    <!-- JS Scripts-->
    <!-- jQuery Js -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- Bootstrap Js -->
    <script src="assets/js/bootstrap.min.js"></script>
     
    <!-- Metis Menu Js -->
    <script src="assets/js/jquery.metisMenu.js"></script>
    <!-- Morris Chart Js -->
    <script src="assets/js/morris/raphael-2.1.0.min.js"></script>
    <script src="assets/js/morris/morris.js"></script>
    
    <script src="assets/js/easypiechart.js"></script>
    <script src="assets/js/easypiechart-data.js"></script>
    
     <script src="assets/js/Lightweight-Chart/jquery.chart.js"></script>
    
    <!-- Custom Js -->
    <script src="assets/js/custom-scripts.js"></script>
      <script>
            $(".addList" ).click(function() {
              var IdVideo = $(this).val();
              console.log("Log ID:" + IdVideo);
              execute(IdVideo);
            });
            $("#addPlay" ).click(function() {
              var IdLink = $("#link").val();
              var arrLink = new Array();
              arrLink = IdLink.split("=");

              console.log("Log Link:" + arrLink[1]);
              execute(arrLink[1]);
            });
     
      </script>
</body>
</html>
