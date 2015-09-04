<?php
/**
 * @author    Rashidov Ruslan <rashidov_r@list.ru>
 * @package   fotogrid
 * @version   1.0.0
 */

namespace tessierashpool\fotogrid;
/**
 * Class generate grid 3x4. Every cell will contain image. Depending on the number of images grid will have different variant.
 */
class FotoGrid
{
	/** @var int Width of main container. */
	protected $mainContWidth;
	/** @var int Height of main container. */
	protected $mainContHeight;
	/** @var int Height of rectangle of one cell. */
	protected $minRectHeight;
	/** @var int Width of rectangle of one cell. */
	protected $minRectWidth;		
	/** @var int Margin between rectangles. */
	public $rectangleMargin;
	/** @var int Number of rectangles in  horizontal. */
	protected $horNum=4;
	/** @var int Number of rectangles in  vertical. */
	protected $verNum=3;
	/**
	 * @var array Multidimensional array with information about rectangles positions. ['array index'=>['type'=>'type of rectangle','v'=> 'position on vertical starts by 0','g'=> 'position on gorizontal starts by 0'],...].
	 */
	protected $arGridInfo = array();
	/** @var array White list of variants. */
	protected $arExcept = array();
	/** @var array Number of variants of grid  for every number of rectangles. */
	protected $arrGridVariants = array();
	/** @var int Selected variant of grid. */
	protected $gridVariant = array();	
	/** @var array Dimensions of rectangles. */
	protected $f = array();	
	/** @var string Path to main directory where images will be stored. */
	protected static $pathImagesStored = '/fgrid';
	/** @var string Path to directory where resized images will be stored. */
	protected $pathToGrid = '';	
	/** @var string Url to directory where resized images will be stored. */
	protected $urlToGrid = '';	

	/**
	 * Initiate default white list of variants(use all variants without exceptions). Initiate number of variants of grid  for every number of rectangles.
	 * Will be call init() method if param width will be passed.
	 * @param mixed $width Main container width(default false).
	 * @param int $margin Margin between rectangles(default 1).
	 */
	function __construct($width=false,$margin = 1){
		if($width)
			$this->init($width,$margin);

		//White list of variants.
		$this->arExcept[1]=array(1);
		$this->arExcept[2]=array(1);
		$this->arExcept[3]=array(1,2,3,4);
		$this->arExcept[4]=array(1,2,3,4,5,6,7,8,9,10);
		$this->arExcept[5]=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17);	
		$this->arExcept[6]=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16);	
		$this->arExcept[7]=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16);	
		$this->arExcept[8]=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15);	
		$this->arExcept[9]=array(1,2,3,4,5,6,7,8,9,10,11);
		$this->arExcept[10]=array(1,2,3,4,5,6,7,8,9,10,11,12);
		$this->arExcept[11]=array(1,2,3,4,5,6,7,8,9);
		$this->arExcept[12]=array(1);
		$this->arExcept[13]=array(1,2,3,4,5,6);
		
		//Number of variants of grid  for every number of rectangles.
		$this->arrGridVariants[1] = 1;
		$this->arrGridVariants[2] = 1;
		$this->arrGridVariants[3] = 4;
		$this->arrGridVariants[4] = 10;
		$this->arrGridVariants[5] = 17;
		$this->arrGridVariants[6] = 16;
		$this->arrGridVariants[7] = 16;
		$this->arrGridVariants[8] = 15;
		$this->arrGridVariants[9] = 11;
		$this->arrGridVariants[10] = 12;
		$this->arrGridVariants[11] = 9;
		$this->arrGridVariants[12] = 1;
		$this->arrGridVariants[13] = 6;			
	}

	/**
	 * Calculate height for main container of grid and heiht/width for smallest rectangle in grid.
	 * Initiate default grid with 12 rectangles.
	 * Initiate dimensions of different type rectangles.
	 * @param int $width Main container width.
	 * @param int $margin Margin between rectangles(default 1).
	 */
	function init($width,$margin = 1){
		$width = intval($width);
		$margin = intval($margin);
		$this->rectangleMargin = $this->margin =  $margin;	
		$this->mainContHeight = floor(($width-$margin*3)/$this->horNum)*$this->verNum+$margin*2;
		$this->mainContWidth  = $width;		
		$this->minRectHeight = $this->minRectWidth = floor(($width-$margin*3)/$this->horNum);		
		
		//Default grid with 12 rectangles.			
		$this->arGridInfo = array(
			0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),
			4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>1),6=>array('type'=>1,'v'=>1,'g'=>2),7=>array('type'=>1,'v'=>1,'g'=>3),
			8=>array('type'=>1,'v'=>2,'g'=>0),9=>array('type'=>1,'v'=>2,'g'=>1),10=>array('type'=>1,'v'=>2,'g'=>2),11=>array('type'=>1,'v'=>2,'g'=>3));		

		//Calculate dimensions for different type of rectangle.
		$this->f[1] = new F1($this->minRectWidth,$this->rectangleMargin);
		$this->f[2] = new F2($this->minRectWidth,$this->rectangleMargin);
		$this->f[3] = new F3($this->minRectWidth,$this->rectangleMargin);
		$this->f[4] = new F4($this->minRectWidth,$this->rectangleMargin);
		$this->f[5] = new F5($this->minRectWidth,$this->rectangleMargin);
		$this->f[6] = new F6($this->minRectWidth,$this->rectangleMargin);
		$this->f[7] = new F7($this->minRectWidth,$this->rectangleMargin);		
		
	}

	/**
	 * Create path to directory and check if directory already exist.
	 * Path patern: self::$pathImagesStored / Main container width / Margin of rectangles / Number of rectangles in grid / Variant of grid / ...
	 * @param string $md5 Summary md5 of all images.
	 * @param int $gridVariant Variant of grid.
	 * @param int $rectCount Number of rectangles in grid.
	 * @return boolean 
	 */
	public function pathExist($md5,$gridVariant,$rectCount){
		$dir_1depht = mb_substr($md5,0,2);
		$dir_2depht = mb_substr($md5,2,2);
		$dir_3depht = mb_substr($md5,4,2);
		$this->urlToGrid = self::$pathImagesStored.'/'.$this->mainContWidth .'/'.$this->rectangleMargin.'/'.$rectCount.'/'.$gridVariant.'/'.$dir_1depht.'/'.$dir_2depht.'/'.$dir_3depht;
		$this->pathToGrid = $_SERVER["DOCUMENT_ROOT"].$this->urlToGrid;
		if(is_dir($this->pathToGrid))
			return true;
		else
			return false;
	}

	/**
	 * Return absolute url to directory where resized images are stored.
	 */
	public function getAbsoluteUrl(){
		return $this->urlToGrid;
	}

	/**
	 * Set path to main folder where images will be stored.
	 * @param string $path Path relative to root directory.
	 */
	public function setDirectoryPath($path = '/fgrid'){
		self::$pathImagesStored = $path;
	}

	/**
	 * Create directory for images.
	 */
	public function createDirectory(){
		if(mkdir($this->pathToGrid, 0777, true))
			return $this->pathToGrid;
		else
			die("Can't create folder for image store.");
	}	

	/**
	 * Resize images and save in the grid directory.
	 * @param array $arImages Array of source images paths.
	 * @param int Variant of grid(optional).	 
	 * @return string Absolute url to folder with resized images
	 */
	public function imageResizeAndSave($arImages, $gridVariant=0){			
		//Check images for types and generate summary md5
		$md5sum = '';
		$arImages = array_values($arImages);
		foreach($arImages as $key => $img){
			//Break if images more than 12
			if($key>12)
				break;			

			$info = getimagesize($img);
			if ($info === FALSE) {
			   die("Unable to determine image type.");
			}

			if (($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) {
			   die("Not a gif/jpeg/png.");
			}
			$md5sum.= md5_file($img).$key;
		}
		$md5sum= md5($md5sum);

		//Generate grid for counted images
		$imageCount = count($arImages);
		$rectCount = 0;
		if($imageCount>12)
			$rectCount = 13;
		else
			$rectCount = $imageCount;
		$this->gridVariant=$gridVariant = $this->rectNumberSelect($rectCount,$gridVariant);	

		//Create path and check if directory exist, if directory exists then skip resize of images
		if(!$this->pathExist($md5sum,$gridVariant,$rectCount))
		{
			//Create folder for images
			$pathToImages = $this->createDirectory();	
			foreach($arImages as $key => $img){	
				//Break if images more than number of rectangles in grid
				if($key>count($this->arGridInfo)-1)
					break;

				$resizedImageWidth = $this->f[$this->arGridInfo[$key]['type']]->w;
				$resizedImageHeight = $this->f[$this->arGridInfo[$key]['type']]->h;

				//Path to source image
				$img_src = $img;	
				//Path to resized image, will save as jpg
				$img_dst = $pathToImages.'/'.$key.'.jpg';
				list($imageCurrentWidth, $imageCurrentHeight) = getimagesize($img_src);
				
				//Determine target images sizes
				$tmpCoef = $resizedImageWidth/$resizedImageHeight;
				if(($tmpCoef*$imageCurrentHeight) <= $imageCurrentWidth)
				{	
					$imageCropWidth = $imageCurrentHeight*$tmpCoef;
					$imageCropHeight = $imageCurrentHeight;					
				}
				else
				{
					$tmpCoef = $resizedImageHeight/$resizedImageWidth;
					$imageCropWidth = $imageCurrentWidth;
					$imageCropHeight = $imageCurrentWidth*$tmpCoef;						
				}
				$imageSrcCoordY = abs($imageCurrentWidth-$imageCropWidth)/2;
				$iamgeSrcCoordX = abs($imageCurrentHeight-$imageCropHeight)/2;					
				
				$image_p = imagecreatetruecolor($resizedImageWidth, $resizedImageHeight);
				$image = imagecreatefromjpeg($img_src);
				imagecopyresampled($image_p, $image, 0, 0, $imageSrcCoordY, $iamgeSrcCoordX, $resizedImageWidth, $resizedImageHeight, $imageCropWidth, $imageCropHeight);								
				imagejpeg($image_p, $img_dst, 100);
			}		
		}	
		return $this->urlToGrid;
	}

	/**
	 * Create grid from images paths array.
	 * @param array $arImages Array of source images paths.
	 * @param int Variant of grid(optional).
	 */
	public function gridFromImgArray($arImages,$gridVariant=0){
		$this->imageResizeAndSave($arImages,$gridVariant);
		$this->grid();
	}

	/**
	 * Set url to directory with resized.
	 * @param string $url Path to folder.
	 */
	public function setUrlToGrid($url){
		$this->urlToGrid = $url;
		$arPath = explode("/",str_replace(self::$pathImagesStored.'/', "", $url));
		$this->init($arPath[0],$arPath[1]);
		$this->gridVariant = $this->rectNumberSelect($arPath[2],$arPath[3]);
	}

	/**
	 * Create grid from url to folder where resized images stored.
	 * @param string $url Path to folder.
	 */
	public function gridFromUrl($url){
		$this->setUrlToGrid($url);
		$this->grid();
	}

	/**
	 * Get full info of grid.
	 * @param string $url Path to folder(optional).
	 */
	public function getGridFullInfo($url=''){
		if($url!='')
			$this->setUrlToGrid($url);
		$array['gridvariant'] = $this->gridVariant;
		$array['url'] = $this->urlToGrid;
		$array['gridinfo'] = $this->arGridInfo;
		$array['rectangles'] = $this->f;
		return $array;
	}

	/**
	 * Generate HTML grid 
	 */
	public function grid(){
		echo "<div style='position:relative;overflow:hidden; background-color:transparent; height:".$this->mainContHeight."px; width:".$this->mainContWidth."px;'>";	
		foreach($this->arGridInfo as $key=>$rectangle)
		{
			$leftM = $rectangle['g']*($this->minRectWidth+$this->rectangleMargin);
			$topM = $rectangle['v']*($this->minRectHeight+$this->rectangleMargin);
			$height = $this->f[$rectangle['type']]->h;
			$width = $this->f[$rectangle['type']]->w;
			echo "<div style='position:absolute; left:".$leftM."px;top:".$topM."px;height:".$height."px;width:".$width."px;background-color:#333;'><img style='height:".$height."px;width:".$width."px;' src='".$this->urlToGrid.'/'.$key.'.jpg'."' alt='' /></div>";
		}
		echo "</div>";	
	}

	/**
	 * View all variant for selected number of images.
	 *@param int $rectCount Number of images/rectangles in grid.
	 */
	public function viewAllVariantsForImageNumber($rectCount){
		if(isset($this->arExcept[$rectCount]))
			foreach($this->arExcept[$rectCount] as $gridVariant)
			{
				$variant = $this->rectNumberSelect($rectCount,$gridVariant);	
				echo '<div style="float:left; margin-right: 10px;margin-bottom: 10px;">';
				echo $variant.')';
					echo "<div style='position:relative;overflow:hidden;background-color:#fff; height:".$this->mainContHeight."px; width:".$this->mainContWidth."px;'>";	
					foreach($this->arGridInfo as $key=>$rectangle)
					{
						$leftM = $rectangle['g']*($this->minRectWidth+$this->rectangleMargin);
						$topM = $rectangle['v']*($this->minRectHeight+$this->rectangleMargin);

						$height = $this->f[$rectangle['type']]->h;
						$width = $this->f[$rectangle['type']]->w;
						echo "<div style='position:absolute; left:".$leftM."px;top:".$topM."px;height:".$height."px;width:".$width."px;background-color:#999;'>".($key+1)."</div>";

					}
					echo "</div>";	
				echo '</div>';
			}
		echo '<div style="clear:both;margin-bottom: 10px;"></div>';
	}

	/**
	 * This function select for how many rectangles will be generated grid and optional parameter will select variant of grid.
	 * @param int $rectanglesNum Number of rectangles in grid.
	 * @param int $variant Variant of grid(optional).
	 */
	protected function rectNumberSelect($rectanglesNum,$variant=0){
		switch($rectanglesNum){
			case 1:
				return $this->rectSize1($variant);
			break;		
			case 2:
				return $this->rectSize2($variant);
			break;
			case 3:
				return $this->rectSize3($variant);
			break;
			case 4:
				return $this->rectSize4($variant);
			break;
			case 5:
				return $this->rectSize5($variant);
			break;
			case 6:
				return $this->rectSize6($variant);
			break;
			case 7:
				return $this->rectSize7($variant);
			break;
			case 8:
				return $this->rectSize8($variant);
			break;
			case 9:
				return $this->rectSize9($variant);
			break;
			case 10:
				return $this->rectSize10($variant);
			break;
			case 11:
				return $this->rectSize11($variant);
			break;
			case 12:
				return $this->rectSize12($variant);
			break;
			//If >12
			case 13:
				return $this->rectSize13($variant);
			break;			
		}
	}

	/**
	 * Get random variant of grid for the passed number of rectangles.
	 * @param int $rectNumber Number of rectangles.
	 * @param int $maxNumOfVariants Maximum number of grid variants for passed number of rectangles.
	 * @param int $gridVariant Certain variant of grid for ignore random pick of variant (default 0).
	 * @return int Variant of grid
	 */
	protected function getGridVariant($rectNumber, $maxNumOfVariants, $gridVariant=0){
		$MAX_NUM = $maxNumOfVariants;
		if($gridVariant>0&&$gridVariant<=$MAX_NUM)
			return $gridVariant;

		$this->arGridInfo = array();
		$randVar = rand(0,count($this->arExcept[$rectNumber])-1);		
		return $this->arExcept[$rectNumber][$randVar];	
	}

	/**
	 * Set white list of variants for grid.
	 * @param int $rectNumber Number of rectangles in grid.
	 * @param array $arWhiteList Array of variants.
	 */
	public function setWhiteList($rectNumber, $arWhiteList=array(1))
	{
		$this->arExcept[$rectNumber] = $arWhiteList;
	}

	/**
	 * Select variant for grid with 1 rectangle in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */
	protected function rectSize1($selGrid = 0)
	{
		$this->arGridInfo = array(0=>array('type'=>7,'v'=>0,'g'=>0));	
		return '1';	
	}	

	/**
	 * Select variant for grid with 2 rectangles in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */
	protected function rectSize2($selGrid = 0)
	{
		$this->arGridInfo = array(0=>array('type'=>6,'v'=>0,'g'=>0),1=>array('type'=>6,'v'=>0,'g'=>2));	
		return '1';	
	}

	/**
	 * Select variant for grid with 3 rectangles in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */
	protected function rectSize3($selGrid = 0)
	{	
		$switchVar = $this->getGridVariant(3,4,$selGrid);
		switch($switchVar){
			case 1:
				$this->arGridInfo = array(0=>array('type'=>6,'v'=>0,'g'=>0),1=>array('type'=>4,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>2,'g'=>2));//#1
			break;
			case 2:
				$this->arGridInfo = array(0=>array('type'=>6,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>4,'v'=>1,'g'=>2));//#2
			break;
			case 3:
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>4,'v'=>1,'g'=>0),2=>array('type'=>6,'v'=>0,'g'=>2));//#3
			break;
			case 4:
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>2,'g'=>0),2=>array('type'=>6,'v'=>0,'g'=>2));//#4
			break;
		}
		return $switchVar;		
	}

	/**
	 * Select variant for grid with 4 rectangles in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */
	protected function rectSize4($selGrid = 0)
	{
		$switchVar = $this->getGridVariant(4,10,$selGrid);	
		switch($switchVar){	
			case 1:
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>4,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>2,'g'=>0),3=>array('type'=>2,'v'=>2,'g'=>2));//#1
			break;	
			case 2:
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>6,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>2,'g'=>0),3=>array('type'=>1,'v'=>2,'g'=>1));//#2
			break;	
			case 3:	
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>2,'g'=>0),3=>array('type'=>4,'v'=>1,'g'=>2));//#3
			break;	
			case 4:	
				$this->arGridInfo = array(0=>array('type'=>5,'v'=>0,'g'=>0),1=>array('type'=>3,'v'=>0,'g'=>3),2=>array('type'=>2,'v'=>2,'g'=>0),3=>array('type'=>2,'v'=>2,'g'=>2));//#4
			break;	
			case 5:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>4,'v'=>1,'g'=>0),3=>array('type'=>6,'v'=>0,'g'=>2));//#5
			break;	
			case 6:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>3,'v'=>1,'g'=>0),3=>array('type'=>5,'v'=>1,'g'=>1));//#6
			break;	
			case 7:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>5,'v'=>1,'g'=>0),3=>array('type'=>3,'v'=>1,'g'=>3));//#7	
			break;	
			case 8:	
				$this->arGridInfo = array(0=>array('type'=>6,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>1,'g'=>2),3=>array('type'=>2,'v'=>2,'g'=>2));//#8	
			break;	
			case 9:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>1,'g'=>0),2=>array('type'=>2,'v'=>2,'g'=>0),3=>array('type'=>6,'v'=>0,'g'=>2));//#9	
			break;	
			case 10:	
				$this->arGridInfo = array(0=>array('type'=>6,'v'=>0,'g'=>0),1=>array('type'=>4,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>2,'g'=>2),3=>array('type'=>1,'v'=>2,'g'=>3));//#10	
			break;	
		}
		return $switchVar;		
	}

	/**
	 * Select variant for grid with 5 rectangles in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */
	protected function rectSize5($selGrid = 0)
	{
		$switchVar = $this->getGridVariant(5,17,$selGrid);			
		switch($switchVar){		
			case 1:
				$this->arGridInfo = array(0=>array('type'=>5,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>3),2=>array('type'=>1,'v'=>1,'g'=>3),3=>array('type'=>2,'v'=>2,'g'=>0),4=>array('type'=>2,'v'=>2,'g'=>2));//#1
			break;	
			case 2:	
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>1,'g'=>2),3=>array('type'=>2,'v'=>2,'g'=>0),4=>array('type'=>2,'v'=>2,'g'=>2));//#2
			break;	
			case 3:	
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>3,'v'=>0,'g'=>2),2=>array('type'=>3,'v'=>0,'g'=>3),3=>array('type'=>2,'v'=>2,'g'=>0),4=>array('type'=>2,'v'=>2,'g'=>2));//#3
			break;	
			case 4:	
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>2,'g'=>0),3=>array('type'=>3,'v'=>1,'g'=>2),4=>array('type'=>3,'v'=>1,'g'=>3));//#4
			break;	
			case 5:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>3,'v'=>1,'g'=>0),3=>array('type'=>3,'v'=>1,'g'=>1),4=>array('type'=>6,'v'=>0,'g'=>2));//#5
			break;	
			case 6:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>3,'v'=>1,'g'=>0),2=>array('type'=>3,'v'=>0,'g'=>1),3=>array('type'=>1,'v'=>2,'g'=>1),4=>array('type'=>6,'v'=>0,'g'=>2));//#6
			break;	
			case 7:	
				$this->arGridInfo = array(0=>array('type'=>3,'v'=>0,'g'=>0),1=>array('type'=>3,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>2,'g'=>0),3=>array('type'=>1,'v'=>2,'g'=>1),4=>array('type'=>6,'v'=>0,'g'=>2));//#7
			break;	
			case 8:	
				$this->arGridInfo = array(0=>array('type'=>5,'v'=>0,'g'=>0),1=>array('type'=>3,'v'=>0,'g'=>3),2=>array('type'=>2,'v'=>2,'g'=>0),3=>array('type'=>1,'v'=>2,'g'=>2),4=>array('type'=>1,'v'=>2,'g'=>3));//#8
			break;	
			case 9:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>5,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>3),4=>array('type'=>1,'v'=>2,'g'=>3));//#9
			break;	
			case 10:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>2,'g'=>0),4=>array('type'=>5,'v'=>1,'g'=>1));//#10
			break;	
			case 11:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>1,'g'=>0),2=>array('type'=>1,'v'=>2,'g'=>0),3=>array('type'=>1,'v'=>2,'g'=>1),4=>array('type'=>6,'v'=>0,'g'=>2));//#11
			break;	
			case 12:	
				$this->arGridInfo = array(0=>array('type'=>6,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>1,'g'=>2),3=>array('type'=>1,'v'=>2,'g'=>2),4=>array('type'=>1,'v'=>2,'g'=>3));//#12
			break;	
			case 13:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>1,'g'=>0),2=>array('type'=>5,'v'=>0,'g'=>1),3=>array('type'=>2,'v'=>2,'g'=>0),4=>array('type'=>2,'v'=>2,'g'=>2));//#13		
			break;	
			case 14:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>1,'g'=>0),3=>array('type'=>2,'v'=>2,'g'=>0),4=>array('type'=>4,'v'=>1,'g'=>2));//#14
			break;	
			case 15:	
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>4,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>2,'g'=>0),3=>array('type'=>1,'v'=>2,'g'=>1),4=>array('type'=>2,'v'=>2,'g'=>2));//#15		
			break;	
			case 16:	
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>2,'v'=>2,'g'=>0),4=>array('type'=>4,'v'=>1,'g'=>2));//#16
			break;	
			case 17:	
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>2,'g'=>0),3=>array('type'=>1,'v'=>2,'g'=>1),4=>array('type'=>4,'v'=>1,'g'=>2));//#17
			break;	
		}	
		return $switchVar;			
	}	

	/**
	 * Select variant for grid with 6 rectangles in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */
	protected function rectSize6($selGrid = 0)
	{
		$switchVar = $this->getGridVariant(6,16,$selGrid);			
		switch($switchVar){	
			case 1:
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>1),4=>array('type'=>2,'v'=>2,'g'=>0),5=>array('type'=>6,'v'=>0,'g'=>2));//#1
			break;	
			case 2:
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>2,'v'=>1,'g'=>2),4=>array('type'=>2,'v'=>2,'g'=>0),5=>array('type'=>2,'v'=>2,'g'=>2));//#2
			break;	
			case 3:
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>1,'g'=>0),2=>array('type'=>5,'v'=>0,'g'=>1),3=>array('type'=>1,'v'=>2,'g'=>0),4=>array('type'=>1,'v'=>2,'g'=>1),5=>array('type'=>2,'v'=>2,'g'=>2));//#3
			break;	
			case 4:
				$this->arGridInfo = array(0=>array('type'=>5,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>3),2=>array('type'=>1,'v'=>1,'g'=>3),3=>array('type'=>2,'v'=>2,'g'=>0),4=>array('type'=>1,'v'=>2,'g'=>2),5=>array('type'=>1,'v'=>2,'g'=>3));//#4
			break;	
			case 5:	
				$this->arGridInfo = array(0=>array('type'=>5,'v'=>0,'g'=>0),1=>array('type'=>3,'v'=>0,'g'=>3),2=>array('type'=>1,'v'=>2,'g'=>0),3=>array('type'=>1,'v'=>2,'g'=>1),4=>array('type'=>1,'v'=>2,'g'=>2),5=>array('type'=>1,'v'=>2,'g'=>3));//#5
			break;	
			case 6:	
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>4,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>2,'g'=>0),3=>array('type'=>1,'v'=>2,'g'=>1),4=>array('type'=>1,'v'=>2,'g'=>2),5=>array('type'=>1,'v'=>2,'g'=>3));//#6
			break;	
			case 7:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>4,'v'=>1,'g'=>0),3=>array('type'=>4,'v'=>0,'g'=>2),4=>array('type'=>1,'v'=>2,'g'=>2),5=>array('type'=>1,'v'=>2,'g'=>3));//#7
			break;	
			case 8:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>4,'v'=>1,'g'=>0),5=>array('type'=>4,'v'=>1,'g'=>2));//#8
			break;	
			case 9:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>2,'v'=>0,'g'=>2),3=>array('type'=>3,'v'=>1,'g'=>0),4=>array('type'=>3,'v'=>1,'g'=>1),5=>array('type'=>4,'v'=>1,'g'=>2));//#9
			break;	
			case 10:	
				$this->arGridInfo = array(0=>array('type'=>3,'v'=>0,'g'=>0),1=>array('type'=>3,'v'=>0,'g'=>1),2=>array('type'=>3,'v'=>0,'g'=>2),3=>array('type'=>3,'v'=>0,'g'=>3),4=>array('type'=>2,'v'=>2,'g'=>0),5=>array('type'=>2,'v'=>2,'g'=>2));//#10
			break;	
			case 11:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>3,'v'=>1,'g'=>0),3=>array('type'=>3,'v'=>1,'g'=>1),4=>array('type'=>3,'v'=>1,'g'=>2),5=>array('type'=>3,'v'=>1,'g'=>3));//#11
			break;	
			case 12:	
				$this->arGridInfo = array(0=>array('type'=>6,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>1,'v'=>1,'g'=>2),4=>array('type'=>1,'v'=>1,'g'=>3),5=>array('type'=>2,'v'=>2,'g'=>2));//#12
			break;	
			case 13:	
				$this->arGridInfo = array(0=>array('type'=>6,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>1,'g'=>2),3=>array('type'=>1,'v'=>1,'g'=>3),4=>array('type'=>1,'v'=>2,'g'=>2),5=>array('type'=>1,'v'=>2,'g'=>3));//#13
			break;	
			case 14:	
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>1,'g'=>2),3=>array('type'=>1,'v'=>2,'g'=>0),4=>array('type'=>1,'v'=>2,'g'=>1),5=>array('type'=>2,'v'=>2,'g'=>2));//#14
			break;	
			case 15:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>1,'g'=>0),3=>array('type'=>2,'v'=>1,'g'=>2),4=>array('type'=>2,'v'=>2,'g'=>0),5=>array('type'=>2,'v'=>2,'g'=>2));//#15
			break;	
			case 16:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>1,'g'=>0),2=>array('type'=>1,'v'=>1,'g'=>1),3=>array('type'=>1,'v'=>2,'g'=>0),4=>array('type'=>1,'v'=>2,'g'=>1),5=>array('type'=>6,'v'=>0,'g'=>2));//#16
			break;	
		}
		return $switchVar;		
	}	

	/**
	 * Select variant for grid with 7 rectangles in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */
	protected function rectSize7($selGrid = 0)
	{
		$switchVar = $this->getGridVariant(7,16,$selGrid);
		switch($switchVar){				
			case 1:
				$this->arGridInfo = array(0=>array('type'=>6,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>1,'v'=>1,'g'=>2),4=>array('type'=>1,'v'=>1,'g'=>3),5=>array('type'=>1,'v'=>2,'g'=>2),6=>array('type'=>1,'v'=>2,'g'=>3));//#1
			break;	
			case 2:	
				$this->arGridInfo = array(0=>array('type'=>5,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>3),2=>array('type'=>1,'v'=>1,'g'=>3),3=>array('type'=>1,'v'=>2,'g'=>0),4=>array('type'=>1,'v'=>2,'g'=>1),5=>array('type'=>1,'v'=>2,'g'=>2),6=>array('type'=>1,'v'=>2,'g'=>3));//#2
			break;	
			case 3:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>1),4=>array('type'=>1,'v'=>2,'g'=>0),5=>array('type'=>1,'v'=>2,'g'=>1),6=>array('type'=>6,'v'=>0,'g'=>2));//#3
			break;	
			case 4:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>5,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>3),6=>array('type'=>1,'v'=>2,'g'=>3));//#4	
			break;	
			case 5:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>2,'g'=>0),6=>array('type'=>5,'v'=>1,'g'=>1));//#5	
			break;	
			case 6:	
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>1,'v'=>1,'g'=>2),4=>array('type'=>1,'v'=>1,'g'=>3),5=>array('type'=>2,'v'=>2,'g'=>0),6=>array('type'=>2,'v'=>2,'g'=>2));//#6	
			break;	
			case 7:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>3,'v'=>1,'g'=>0),5=>array('type'=>3,'v'=>1,'g'=>1),6=>array('type'=>4,'v'=>1,'g'=>2));//#7	
			break;	
			case 8:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>1,'g'=>0),2=>array('type'=>4,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>2,'g'=>0),4=>array('type'=>1,'v'=>2,'g'=>1),5=>array('type'=>1,'v'=>2,'g'=>2),6=>array('type'=>1,'v'=>2,'g'=>3));//#8	
			break;	
			case 9:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>1),4=>array('type'=>1,'v'=>2,'g'=>0),5=>array('type'=>1,'v'=>2,'g'=>1),6=>array('type'=>4,'v'=>1,'g'=>2));//#9	
			break;	
			case 10:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>1,'g'=>0),3=>array('type'=>2,'v'=>1,'g'=>2),4=>array('type'=>2,'v'=>2,'g'=>0),5=>array('type'=>1,'v'=>2,'g'=>2),6=>array('type'=>1,'v'=>2,'g'=>3));//#10	
			break;	
			case 11:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>4,'v'=>1,'g'=>0),5=>array('type'=>3,'v'=>1,'g'=>2),6=>array('type'=>3,'v'=>1,'g'=>3));//#11
			break;	
			case 12:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>1,'g'=>0),3=>array('type'=>2,'v'=>1,'g'=>2),4=>array('type'=>1,'v'=>2,'g'=>0),5=>array('type'=>1,'v'=>2,'g'=>1),6=>array('type'=>2,'v'=>2,'g'=>2));//#12	
			break;	
			case 13:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>2,'v'=>0,'g'=>2),3=>array('type'=>2,'v'=>1,'g'=>0),4=>array('type'=>2,'v'=>1,'g'=>2),5=>array('type'=>2,'v'=>2,'g'=>0),6=>array('type'=>2,'v'=>2,'g'=>2));//#13	
			break;	
			case 14:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>4,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>2),4=>array('type'=>1,'v'=>1,'g'=>3),5=>array('type'=>1,'v'=>2,'g'=>2),6=>array('type'=>1,'v'=>2,'g'=>3));//#14	
			break;	
			case 15:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>1),4=>array('type'=>4,'v'=>0,'g'=>2),5=>array('type'=>2,'v'=>2,'g'=>0),6=>array('type'=>2,'v'=>2,'g'=>2));//#15
			break;	
			case 16:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>1,'g'=>0),2=>array('type'=>5,'v'=>0,'g'=>1),3=>array('type'=>1,'v'=>2,'g'=>0),4=>array('type'=>1,'v'=>2,'g'=>1),5=>array('type'=>1,'v'=>2,'g'=>2),6=>array('type'=>1,'v'=>2,'g'=>3));//#16		
			break;	
		}
		return $switchVar;		
	}	

	/**
	 * Select variant for grid with 8 rectangles in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */
	protected function rectSize8($selGrid = 0)
	{
		$switchVar = $this->getGridVariant(8,15,$selGrid);
		switch($switchVar){			
			case 1:
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>1,'v'=>1,'g'=>2),4=>array('type'=>1,'v'=>1,'g'=>3),5=>array('type'=>2,'v'=>2,'g'=>0),6=>array('type'=>1,'v'=>2,'g'=>2),7=>array('type'=>1,'v'=>2,'g'=>3));//#1
			break;	
			case 2:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>4,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>2),5=>array('type'=>1,'v'=>1,'g'=>3),6=>array('type'=>1,'v'=>2,'g'=>2),7=>array('type'=>1,'v'=>2,'g'=>3));//#2
			break;	
			case 3:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>2,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>1),5=>array('type'=>1,'v'=>2,'g'=>0),6=>array('type'=>1,'v'=>2,'g'=>1),7=>array('type'=>4,'v'=>1,'g'=>2));//#3
			break;	
			case 4:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>1),4=>array('type'=>4,'v'=>0,'g'=>2),5=>array('type'=>1,'v'=>2,'g'=>0),6=>array('type'=>1,'v'=>2,'g'=>1),7=>array('type'=>2,'v'=>2,'g'=>2));//#4
			break;	
			case 5:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>2,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>1),5=>array('type'=>2,'v'=>1,'g'=>2),6=>array('type'=>2,'v'=>2,'g'=>0),7=>array('type'=>2,'v'=>2,'g'=>2));//#5
			break;	
			case 6:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>1),4=>array('type'=>2,'v'=>1,'g'=>2),5=>array('type'=>1,'v'=>2,'g'=>0),6=>array('type'=>1,'v'=>2,'g'=>1),7=>array('type'=>2,'v'=>2,'g'=>2));//#6
			break;	
			case 7:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>2,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>2),5=>array('type'=>1,'v'=>1,'g'=>3),6=>array('type'=>2,'v'=>2,'g'=>0),7=>array('type'=>2,'v'=>2,'g'=>2));//#7
			break;	
			case 8:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>2),4=>array('type'=>1,'v'=>1,'g'=>3),5=>array('type'=>2,'v'=>2,'g'=>0),6=>array('type'=>1,'v'=>2,'g'=>2),7=>array('type'=>1,'v'=>2,'g'=>3));//#8
			break;	
			case 9:	
				$this->arGridInfo = array(0=>array('type'=>3,'v'=>0,'g'=>0),1=>array('type'=>3,'v'=>0,'g'=>1),2=>array('type'=>3,'v'=>0,'g'=>2),3=>array('type'=>3,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>2,'g'=>0),5=>array('type'=>1,'v'=>2,'g'=>1),6=>array('type'=>1,'v'=>2,'g'=>2),7=>array('type'=>1,'v'=>2,'g'=>3));//#9
			break;	
			case 10:	
				$this->arGridInfo = array(0=>array('type'=>3,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>2,'g'=>0),3=>array('type'=>3,'v'=>1,'g'=>1),4=>array('type'=>3,'v'=>0,'g'=>2),5=>array('type'=>1,'v'=>0,'g'=>3),6=>array('type'=>1,'v'=>2,'g'=>2),7=>array('type'=>3,'v'=>1,'g'=>3));//#10
			break;	
			case 11:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>2,'v'=>1,'g'=>0),3=>array('type'=>2,'v'=>1,'g'=>2),4=>array('type'=>1,'v'=>2,'g'=>0),5=>array('type'=>1,'v'=>2,'g'=>1),6=>array('type'=>1,'v'=>2,'g'=>2),7=>array('type'=>1,'v'=>2,'g'=>3));//#11
			break;	
			case 12:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>2,'v'=>1,'g'=>0),5=>array('type'=>2,'v'=>1,'g'=>2),6=>array('type'=>2,'v'=>2,'g'=>0),7=>array('type'=>2,'v'=>2,'g'=>2));//#12
			break;	
			case 13:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>2,'v'=>0,'g'=>2),3=>array('type'=>2,'v'=>1,'g'=>0),4=>array('type'=>2,'v'=>1,'g'=>2),5=>array('type'=>2,'v'=>2,'g'=>0),6=>array('type'=>1,'v'=>2,'g'=>2),7=>array('type'=>1,'v'=>2,'g'=>3));//#13
			break;	
			case 14:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>3,'v'=>1,'g'=>0),5=>array('type'=>3,'v'=>1,'g'=>1),6=>array('type'=>3,'v'=>1,'g'=>2),7=>array('type'=>3,'v'=>1,'g'=>3));//#14
			break;	
			case 15:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>1),4=>array('type'=>1,'v'=>1,'g'=>2),5=>array('type'=>1,'v'=>1,'g'=>3),6=>array('type'=>2,'v'=>2,'g'=>0),7=>array('type'=>2,'v'=>2,'g'=>2));//#15	
			break;	
		}
		return $switchVar;		
	}

	/**
	 * Select variant for grid with 9 rectangles in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */
	protected function rectSize9($selGrid = 0)
	{
		$switchVar = $this->getGridVariant(9,11,$selGrid);
		switch($switchVar){		
			case 1:
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>1,'v'=>1,'g'=>2),4=>array('type'=>1,'v'=>1,'g'=>3),5=>array('type'=>1,'v'=>2,'g'=>0),6=>array('type'=>1,'v'=>2,'g'=>1),7=>array('type'=>1,'v'=>2,'g'=>2),8=>array('type'=>1,'v'=>2,'g'=>3));//#1
			break;	
			case 2:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>1),4=>array('type'=>4,'v'=>0,'g'=>2),5=>array('type'=>1,'v'=>2,'g'=>0),6=>array('type'=>1,'v'=>2,'g'=>1),7=>array('type'=>1,'v'=>2,'g'=>2),8=>array('type'=>1,'v'=>2,'g'=>3));//#2	
			break;	
			case 3:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>2,'v'=>0,'g'=>3),4=>array('type'=>4,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>2),6=>array('type'=>1,'v'=>1,'g'=>3),7=>array('type'=>1,'v'=>2,'g'=>2),8=>array('type'=>1,'v'=>2,'g'=>3));//#3	
			break;	
			case 4:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>1),6=>array('type'=>1,'v'=>2,'g'=>0),7=>array('type'=>1,'v'=>2,'g'=>1),8=>array('type'=>4,'v'=>1,'g'=>2));//#4	
			break;	
			case 5:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>1),4=>array('type'=>1,'v'=>1,'g'=>2),5=>array('type'=>1,'v'=>2,'g'=>0),6=>array('type'=>1,'v'=>2,'g'=>1),7=>array('type'=>1,'v'=>2,'g'=>2),8=>array('type'=>3,'v'=>1,'g'=>3));//#5	
			break;	
			case 6:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>2,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>1),5=>array('type'=>2,'v'=>1,'g'=>2),6=>array('type'=>1,'v'=>2,'g'=>0),7=>array('type'=>1,'v'=>2,'g'=>1),8=>array('type'=>2,'v'=>2,'g'=>2));//#6	
			break;	
			case 7:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>2,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>2),5=>array('type'=>1,'v'=>1,'g'=>3),6=>array('type'=>2,'v'=>2,'g'=>0),7=>array('type'=>1,'v'=>2,'g'=>2),8=>array('type'=>1,'v'=>2,'g'=>3));//#7	
			break;	
			case 8:	
				$this->arGridInfo = array(0=>array('type'=>3,'v'=>0,'g'=>0),1=>array('type'=>3,'v'=>0,'g'=>1),2=>array('type'=>3,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>3),5=>array('type'=>1,'v'=>2,'g'=>0),6=>array('type'=>1,'v'=>2,'g'=>1),7=>array('type'=>1,'v'=>2,'g'=>2),8=>array('type'=>1,'v'=>2,'g'=>3));//#8	
			break;	
			case 9:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>2,'g'=>0),6=>array('type'=>3,'v'=>1,'g'=>1),7=>array('type'=>3,'v'=>1,'g'=>2),8=>array('type'=>3,'v'=>1,'g'=>3));//#9	
			break;	
			case 10:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>1),6=>array('type'=>2,'v'=>1,'g'=>2),7=>array('type'=>2,'v'=>2,'g'=>0),8=>array('type'=>2,'v'=>2,'g'=>2));//#10	
			break;	
			case 11:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>3,'v'=>1,'g'=>0),5=>array('type'=>3,'v'=>1,'g'=>1),6=>array('type'=>3,'v'=>1,'g'=>2),7=>array('type'=>1,'v'=>1,'g'=>3),8=>array('type'=>1,'v'=>2,'g'=>3));//#11		
			break;	
		}
		return $switchVar;		
	}

	/**
	 * Select variant for grid with 10 rectangles in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */
	protected function rectSize10($selGrid = 0)
	{
		$switchVar = $this->getGridVariant(10,12,$selGrid);
		switch($switchVar){	
			case 1:
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>2,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>2),5=>array('type'=>1,'v'=>1,'g'=>3),6=>array('type'=>1,'v'=>2,'g'=>0),7=>array('type'=>1,'v'=>2,'g'=>1),8=>array('type'=>1,'v'=>2,'g'=>2),9=>array('type'=>1,'v'=>2,'g'=>3));//#1
			break;	
			case 2:	
				$this->arGridInfo = array(0=>array('type'=>3,'v'=>0,'g'=>0),1=>array('type'=>3,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>2),5=>array('type'=>1,'v'=>1,'g'=>3),6=>array('type'=>1,'v'=>2,'g'=>0),7=>array('type'=>1,'v'=>2,'g'=>1),8=>array('type'=>1,'v'=>2,'g'=>2),9=>array('type'=>1,'v'=>2,'g'=>3));//#2
			break;	
			case 3:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>2,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>2),6=>array('type'=>1,'v'=>1,'g'=>3),7=>array('type'=>2,'v'=>2,'g'=>0),8=>array('type'=>1,'v'=>2,'g'=>2),9=>array('type'=>1,'v'=>2,'g'=>3));//#3
			break;	
			case 4:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>3,'v'=>1,'g'=>0),5=>array('type'=>3,'v'=>1,'g'=>1),6=>array('type'=>1,'v'=>1,'g'=>2),7=>array('type'=>1,'v'=>1,'g'=>3),8=>array('type'=>1,'v'=>2,'g'=>2),9=>array('type'=>1,'v'=>2,'g'=>3));//#4
			break;	
			case 5:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>2,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>1,'g'=>0),3=>array('type'=>1,'v'=>1,'g'=>1),4=>array('type'=>1,'v'=>1,'g'=>2),5=>array('type'=>1,'v'=>1,'g'=>3),6=>array('type'=>1,'v'=>2,'g'=>0),7=>array('type'=>1,'v'=>2,'g'=>1),8=>array('type'=>1,'v'=>2,'g'=>2),9=>array('type'=>1,'v'=>2,'g'=>3));//#5
			break;	
			case 6:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>1),6=>array('type'=>1,'v'=>1,'g'=>2),7=>array('type'=>1,'v'=>1,'g'=>3),8=>array('type'=>2,'v'=>2,'g'=>0),9=>array('type'=>2,'v'=>2,'g'=>2));//#6
			break;	
			case 7:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>1),6=>array('type'=>2,'v'=>1,'g'=>2),7=>array('type'=>1,'v'=>2,'g'=>0),8=>array('type'=>1,'v'=>2,'g'=>1),9=>array('type'=>2,'v'=>2,'g'=>2));//#7
			break;	
			case 8:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>1),6=>array('type'=>1,'v'=>2,'g'=>0),7=>array('type'=>1,'v'=>2,'g'=>1),8=>array('type'=>3,'v'=>1,'g'=>2),9=>array('type'=>3,'v'=>1,'g'=>3));//#8
			break;	
			case 9:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>2,'v'=>1,'g'=>0),5=>array('type'=>2,'v'=>1,'g'=>2),6=>array('type'=>1,'v'=>2,'g'=>0),7=>array('type'=>1,'v'=>2,'g'=>1),8=>array('type'=>1,'v'=>2,'g'=>2),9=>array('type'=>1,'v'=>2,'g'=>3));//#9
			break;	
			case 10:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>2,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>1),5=>array('type'=>1,'v'=>1,'g'=>2),6=>array('type'=>1,'v'=>1,'g'=>3),7=>array('type'=>2,'v'=>2,'g'=>0),8=>array('type'=>1,'v'=>2,'g'=>2),9=>array('type'=>1,'v'=>2,'g'=>3));//#10
			break;	
			case 11:	
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>1,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>1),5=>array('type'=>1,'v'=>1,'g'=>2),6=>array('type'=>1,'v'=>1,'g'=>3),7=>array('type'=>1,'v'=>2,'g'=>0),8=>array('type'=>1,'v'=>2,'g'=>1),9=>array('type'=>2,'v'=>2,'g'=>2));//#11
			break;	
			case 12:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>2,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>1),5=>array('type'=>2,'v'=>1,'g'=>2),6=>array('type'=>1,'v'=>2,'g'=>0),7=>array('type'=>1,'v'=>2,'g'=>1),8=>array('type'=>1,'v'=>2,'g'=>2),9=>array('type'=>1,'v'=>2,'g'=>3));//#12		
			break;	
		}
		return $switchVar;	
	}

	/**
	 * Select variant for grid with 11 rectangles in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */
	protected function rectSize11($selGrid = 0)
	{
		$switchVar = $this->getGridVariant(11,9,$selGrid);
		switch($switchVar){			
			case 1:
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>1,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>1),5=>array('type'=>1,'v'=>1,'g'=>2),6=>array('type'=>1,'v'=>1,'g'=>3),7=>array('type'=>1,'v'=>2,'g'=>0),8=>array('type'=>1,'v'=>2,'g'=>1),9=>array('type'=>1,'v'=>2,'g'=>2),10=>array('type'=>1,'v'=>2,'g'=>3));//#1
			break;	
			case 2:		
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>1),6=>array('type'=>1,'v'=>1,'g'=>2),7=>array('type'=>1,'v'=>1,'g'=>3),8=>array('type'=>2,'v'=>2,'g'=>0),9=>array('type'=>1,'v'=>2,'g'=>2),10=>array('type'=>1,'v'=>2,'g'=>3));//#2
			break;	
			case 3:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>1),6=>array('type'=>1,'v'=>1,'g'=>2),7=>array('type'=>1,'v'=>1,'g'=>3),8=>array('type'=>1,'v'=>2,'g'=>0),9=>array('type'=>1,'v'=>2,'g'=>1),10=>array('type'=>2,'v'=>2,'g'=>2));//#3
			break;	
			case 4:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>1),6=>array('type'=>1,'v'=>1,'g'=>2),7=>array('type'=>1,'v'=>2,'g'=>0),8=>array('type'=>1,'v'=>2,'g'=>1),9=>array('type'=>1,'v'=>2,'g'=>2),10=>array('type'=>3,'v'=>1,'g'=>3));//#4
			break;	
			case 5:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>1),5=>array('type'=>1,'v'=>1,'g'=>2),6=>array('type'=>3,'v'=>0,'g'=>3),7=>array('type'=>1,'v'=>2,'g'=>0),8=>array('type'=>1,'v'=>2,'g'=>1),9=>array('type'=>1,'v'=>2,'g'=>2),10=>array('type'=>1,'v'=>2,'g'=>3));//#5
			break;	
			case 6:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>2,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>1),5=>array('type'=>1,'v'=>1,'g'=>2),6=>array('type'=>1,'v'=>1,'g'=>3),7=>array('type'=>1,'v'=>2,'g'=>0),8=>array('type'=>1,'v'=>2,'g'=>1),9=>array('type'=>1,'v'=>2,'g'=>2),10=>array('type'=>1,'v'=>2,'g'=>3));//#6
			break;	
			case 7:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>2,'v'=>1,'g'=>1),6=>array('type'=>1,'v'=>1,'g'=>3),7=>array('type'=>1,'v'=>2,'g'=>0),8=>array('type'=>1,'v'=>2,'g'=>1),9=>array('type'=>1,'v'=>2,'g'=>2),10=>array('type'=>1,'v'=>2,'g'=>3));//#7
			break;	
			case 8:	
				$this->arGridInfo = array(0=>array('type'=>3,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>1),5=>array('type'=>1,'v'=>1,'g'=>2),6=>array('type'=>1,'v'=>1,'g'=>3),7=>array('type'=>1,'v'=>2,'g'=>0),8=>array('type'=>1,'v'=>2,'g'=>1),9=>array('type'=>1,'v'=>2,'g'=>2),10=>array('type'=>1,'v'=>2,'g'=>3));//#8
			break;	
			case 9:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>3,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>1),6=>array('type'=>1,'v'=>1,'g'=>2),7=>array('type'=>1,'v'=>1,'g'=>3),8=>array('type'=>1,'v'=>2,'g'=>1),9=>array('type'=>1,'v'=>2,'g'=>2),10=>array('type'=>1,'v'=>2,'g'=>3));//#9		
			break;
		}
		return $switchVar;
	}

	/**
	 * Select variant for grid with 12 rectangles in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */
	protected function rectSize12($selGrid = 0)
	{	
		$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),
		5=>array('type'=>1,'v'=>1,'g'=>1),6=>array('type'=>1,'v'=>1,'g'=>2),7=>array('type'=>1,'v'=>1,'g'=>3),8=>array('type'=>1,'v'=>2,'g'=>0),
		9=>array('type'=>1,'v'=>2,'g'=>1),10=>array('type'=>1,'v'=>2,'g'=>2),11=>array('type'=>1,'v'=>2,'g'=>3));//#1		
		return '1';
	}

	/**
	 * Select variant for grid with more then 12 rectangles in grid
	 * @param int $selGrid Variant of grid(optional), if not passed will be picked random variant of grid.
	 */	
	protected function rectSize13($selGrid = 0)
	{
		$switchVar = $this->getGridVariant(13,6,$selGrid);
		switch($switchVar){			
			case 1:
				$this->arGridInfo = array(0=>array('type'=>5,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>3),2=>array('type'=>1,'v'=>1,'g'=>3),3=>array('type'=>1,'v'=>2,'g'=>0),4=>array('type'=>1,'v'=>2,'g'=>1),5=>array('type'=>1,'v'=>2,'g'=>2),6=>array('type'=>1,'v'=>2,'g'=>3));//#1	
			break;
			case 2:
				$this->arGridInfo = array(0=>array('type'=>6,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>1,'v'=>1,'g'=>2),4=>array('type'=>1,'v'=>1,'g'=>3),5=>array('type'=>1,'v'=>2,'g'=>2),6=>array('type'=>1,'v'=>2,'g'=>3));//#2		
			break;
			case 3:
				$this->arGridInfo = array(0=>array('type'=>4,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>1,'v'=>1,'g'=>2),4=>array('type'=>1,'v'=>1,'g'=>3),5=>array('type'=>1,'v'=>2,'g'=>0),6=>array('type'=>1,'v'=>2,'g'=>1),7=>array('type'=>1,'v'=>2,'g'=>2),8=>array('type'=>1,'v'=>2,'g'=>3));//#3		
			break;
			case 4:
				$this->arGridInfo = array(0=>array('type'=>2,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>2),2=>array('type'=>1,'v'=>0,'g'=>3),3=>array('type'=>1,'v'=>1,'g'=>0),4=>array('type'=>1,'v'=>1,'g'=>1),5=>array('type'=>1,'v'=>1,'g'=>2),6=>array('type'=>1,'v'=>1,'g'=>3),7=>array('type'=>1,'v'=>2,'g'=>0),8=>array('type'=>1,'v'=>2,'g'=>1),9=>array('type'=>1,'v'=>2,'g'=>2),10=>array('type'=>1,'v'=>2,'g'=>3));//#4
			break;	
			case 5:	
				$this->arGridInfo = array(0=>array('type'=>3,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>1),5=>array('type'=>1,'v'=>1,'g'=>2),6=>array('type'=>1,'v'=>1,'g'=>3),7=>array('type'=>1,'v'=>2,'g'=>0),8=>array('type'=>1,'v'=>2,'g'=>1),9=>array('type'=>1,'v'=>2,'g'=>2),10=>array('type'=>1,'v'=>2,'g'=>3));//#5	
			break;	
			case 6:	
				$this->arGridInfo = array(0=>array('type'=>1,'v'=>0,'g'=>0),1=>array('type'=>1,'v'=>0,'g'=>1),2=>array('type'=>1,'v'=>0,'g'=>2),3=>array('type'=>1,'v'=>0,'g'=>3),4=>array('type'=>1,'v'=>1,'g'=>0),5=>array('type'=>1,'v'=>1,'g'=>1),6=>array('type'=>1,'v'=>1,'g'=>2),7=>array('type'=>1,'v'=>1,'g'=>3),8=>array('type'=>1,'v'=>2,'g'=>0),9=>array('type'=>1,'v'=>2,'g'=>1),10=>array('type'=>1,'v'=>2,'g'=>2),11=>array('type'=>1,'v'=>2,'g'=>3));//#6		
			break;	
		}
		return $switchVar;
	}	
}

/**
 * Base class for rectangles.
 */
class Rectangle{
	/** @var int Rectangle width.*/
	public $w;
	/** @var int Rectangle height.*/
	public $h;	
	/**
	 * Calculate rectangle dimensions.
	 * @param int $w Rectangle width.
	 * @param int $m Rectangle margin.
	 */
	function __construct($w,$m){}		
}

// #
/**
 * Rectangle of one cell.
 */
class F1 extends Rectangle{
	function __construct($w,$m)
	{
		$h = $w;
		$this->h = $h;
		$this->w = $w;
	
	}
}

// ##
/**
 * Rectangle of two cells, 1x2.
 */
class F2 extends Rectangle{
	function __construct($w,$m)
	{
		$h = $w;
		$this->h = $h;
		$this->w = $w*2 + $m;	
	}		
}

// #
// #	
/**
 * Rectangle of two cells, 2x1.		
 */	
class F3 extends Rectangle{
	function __construct($w,$m)
	{
		$h = $w;
		$this->h = $h*2 + $m;
		$this->w = $w;	
	}			
}

// ##
// ##
/**
 * Rectangle of 4 cells, 2x2.		
 */	
class F4 extends Rectangle{	
	function __construct($w,$m)
	{
		$h = $w;		
		$this->h = $h*2 + $m;
		$this->w = $w*2 + $m;	
	}	
}

// ####
// ####	
/**
 * Rectangle of 6 cells, 2x3.		
 */	
class F5 extends Rectangle{	
	function __construct($w,$m)
	{
		$h = $w;
		$this->h = $h*2 + $m;
		$this->w = $w*3 + $m*2;
	
	}			
}

// ##
// ##
// ##
/**
 * Rectangle of 6 cells, 3x2.	
 */	
class F6 extends Rectangle{
	function __construct($w,$m)
	{
		$h = $w;
		$this->h = $h*3 + $m*2;
		$this->w = $w*2 + $m;
	
	}		
}

// ####
// ####	
// ####
/**
 * Rectangle of 12 cells, 3x4.	
 */
class F7{
	function __construct($w,$m)
	{
		$h = $w;
		$this->h = $h*3 + $m*2;
		$this->w = $w*4 + $m*3;
	
	}		
}