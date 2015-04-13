package  {
	
	// FLASH PACKAGES
	import flash.display.Bitmap;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.text.TextField;
	import flash.text.TextFormat;
    import mx.preloaders.*; 
    import flash.events.ProgressEvent;

    public class ManaganaSplash extends SparkDownloadProgressBar {
		
		// CONSTANTS
		
		private const VERSION:String = "1.7.0 (beta 08/04/15 14:00)";		// current Managana version number
		
		// VARIABLES
		
		[Embed(source="ManaganaSplash.png")]
        private var SplashBG:Class;		// the splash image
		
        public function ManaganaSplash() {
            super();
			// wait for the stage
			this.addEventListener(Event.ADDED_TO_STAGE, onStage);
        }
		
		/**
		 * The stage became available.
		 */
		private function onStage(evt:Event):void {
			this.removeEventListener(Event.ADDED_TO_STAGE, onStage);
			var img:Sprite = new Sprite();
			img.addChild(new SplashBG() as Bitmap);
			img.x = (this.stage.stageWidth - img.width) / 2;
			img.y = (this.stage.stageHeight / 2) - img.height;
			this.addChild(img);
			this.y = img.height / 2;
			var versionNum:TextField = new TextField();
			versionNum.defaultTextFormat = new TextFormat("_sans", 14, 0x000000, null, null, null, "http://www.managana.org/", "_blank", "center");
			versionNum.text = VERSION;
			versionNum.width = 300;
			versionNum.height = 25;
			versionNum.x = (this.stage.stageWidth - versionNum.width) / 2;
			versionNum.y = img.y + img.height + 20;
			this.addChild(versionNum);
		}
        
		/**
		 * Override to return true so progress bar appears during initialization.
		 */
        override protected function showDisplayForInit(elapsedTime:int, count:int):Boolean {
			return (true);
        }
		
		/**
		 * Override to return true so progress bar appears during download.
		 */
        // 
        override protected function showDisplayForDownloading(elapsedTime:int, event:ProgressEvent):Boolean {
			return (true);
        }
    }
}