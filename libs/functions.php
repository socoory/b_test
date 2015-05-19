<?php



function createThumb($path, $src, $dstWidth, $dstHeight) {
    list($w,$h, $type) = getimagesize($path."/".$src);
	
	switch($type) {
		case 1:
			$img = imagecreatefromgif($path."/".$src);
			$type = "imagegif";
			break;
		case 2:
			$img = imagecreatefromjpeg($path."/".$src);
			$type = "imagejpeg";
			break;
		case 3:
			$img = imagecreatefrompng($path."/".$src);
			$type = "imagepng";
			break;
		case 6:
			$img = imagecreatefrombmp($path."/".$src);
			$type = "imagejpeg";
			break;
		default: return false;
	}
	
	// 작은 변을 기준으로 먼저 보정
    if($w > $h) {
        $width = round($dstHeight*$w/$h);
        $height = $dstHeight;
    }
    else {
        $width = $dstWidth;
        $height = round($dstWidth*$h/$w);
    }
	
	// 긴 변의 길이가 썸네일의 해당 변의 길이보다 작을 경우 맞춰서 보정  
    if($width < $dstWidth) {
		$width = round($width*$dstWidth/$width);
		$height = round($height*$dstWidth/$width);
    }
    else if($height < $dstHeight) {
		$width = round($width*$dstHeight/$height);
		$height = round($height*$dstHeight/$height);
    }
	
	// 시작 점의 위치를 계산
    $srcX = round(($width - $dstWidth)/2);
    $srcY = round(($height - $dstHeight)/2);
	
	// 이미지 리사이즈 복제
    $thumb = imagecreatetruecolor($dstWidth, $dstHeight);
    $tempImg = imagecreatetruecolor($width, $height);
	imagecopyresized($tempImg, $img, 0, 0, 0, 0, $width, $height, $w, $h);
	imagecopy($thumb, $tempImg, 0, 0, $srcX, $srcY, $dstWidth, $dstHeight);
	
	// 썸네일저장
	$thumbName = "thumb_".$dstWidth."x".$dstHeight."_".$src;
	$type($thumb, $path."/".$thumbName);
	
    return $path."/".$thumbName;
}

function imagecreatefrombmp($p_sFile) {
    $file = fopen($p_sFile, "rb");
    $read = fread($file, 10);
    while (!feof($file) && ($read <> ""))
        $read .= fread($file, 1024);
    $temp = unpack("H*", $read);
    $hex = $temp[1];
    $header = substr($hex, 0, 108);
    if (substr($header, 0, 4) == "424d") {
        $header_parts = str_split($header, 2);
        $width = hexdec($header_parts[19] . $header_parts[18]);
        $height = hexdec($header_parts[23] . $header_parts[22]);
        unset($header_parts);
    }
    $x = 0;
    $y = 1;
    $image = imagecreatetruecolor($width, $height);
    $body = substr($hex, 108);
    $body_size = (strlen($body) / 2);
    $header_size = ($width * $height);
    $usePadding = ($body_size > ($header_size * 3) + 4);
    for ($i = 0; $i < $body_size; $i+=3) {
        if ($x >= $width) {
            if ($usePadding)
                $i += $width % 4;
            $x = 0;
            $y++;
            if ($y > $height)
                break;
        }
        $i_pos = $i * 2;
        $r = hexdec($body[$i_pos + 4] . $body[$i_pos + 5]);
        $g = hexdec($body[$i_pos + 2] . $body[$i_pos + 3]);
        $b = hexdec($body[$i_pos] . $body[$i_pos + 1]);
        $color = imagecolorallocate($image, $r, $g, $b);
        imagesetpixel($image, $x, $height - $y, $color);
        $x++;
    }
    unset($body);
    return $image;
}

function getThumb($src, $w=100, $h=100) {
	$pathInfo = pathinfo($src);
	return $pathInfo['dirname'].'/thumb_'.$w.'x'.$h.'_'.$pathInfo['basename'];
}