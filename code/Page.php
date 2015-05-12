<?php
	
	class IQBase_Page extends Extension{				
		
		private static $db = array(
			"NoFollow" => "Boolean",
			"SidebarContent" => "HTMLText",
			"LeftColumn" => "HTMLText",
			"CenterColumn" => "HTMLText",
			"RightColumn" => "HTMLText",
			'AdditionalCode' => 'Text',
			"Target" => "enum('_blank,_new,_parent,_self,_top','_self')",
			'URLRedirects' => 'Text',
			'MetaKeywords' => 'Text'
		);	
		
		private static $defaults = array(
			"Target" => "_self"
		);
		
		public function updateCMSFields(FieldList $fields)
		{
			if( permission::check('ADMIN') ){
				$fields->addFieldToTab("Root.Main", new CheckboxField("NoFollow", "Set nav link to no-follow?"),"MetaDescription");
				$fields->addFieldToTab('Root.Main.Metadata', $keywordsField = new TextareaField('MetaKeywords','Meta Keywords'),"ExtraMeta" );
				$keywordsField->setRows(1);
				$fields->addFieldToTab('Root.Main.Metadata', new TextareaField('URLRedirects','301 Redirects') );
				$fields->addFieldToTab('Root.AdditionalCode', $codeField = new TextareaField('AdditionalCode','Additional HTML/JS/CSS Code',50) );
				$codeField->setRows(45);
			}
					
			if($this->owner->ClassName == "Page"){
				$fields->addFieldToTab("Root.Content.Columns", new HTMLEditorField("LeftColumn", "Left Column Content"));  
				$fields->addFieldToTab("Root.Content.Columns", new HTMLEditorField("CenterColumn", "Center Column Content"));  
				$fields->addFieldToTab("Root.Content.Columns", new HTMLEditorField("RightColumn", "Right Column Content")); 
				$fields->addFieldToTab("Root.Content.Sidebar", new HTMLEditorField("SidebarContent", "Sidebar Content"));
			}
				
			return $fields;
		}
		
		public function updateSettingsFields(FieldList $fields)
		{
			$fields->addFieldToTab("Root.Settings", new DropdownField("Target", "Link Target", array(
				"_self"=>"Same Tab",
				"_blank"=>"New Tab"
			)));
			
			return $fields;
		}
	}
	
	class IQBase_Page_Controller extends Extension {
		
		private static $allowed_actions = array(
			"thanks"			
		);
			
		public function onAfterInit() 
		{
			/*
			 * Custom handler to redirect invalid url's to the home page, so we never have a 404 error
			 * will first check if there is already a redirect provided for a specific page
			 */
			if ($this->owner->ErrorCode == 404)
			{
				$RedirectPage = $this->FindRedirectPage();
				$response = new SS_HTTPResponse_Exception();
				$response->getResponse()->redirect($RedirectPage?$RedirectPage->Link():"/", 301);
				throw $response;
			}
			
			$dir = "themes/mysite";
			Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");			
			Requirements::set_combined_files_folder($dir.'/combined');
			
			Requirements::css($dir."/css/fonts.css");
			
			$BaseCSS = array(
				$dir."/css/base.css",
				$dir."/css/fontawesome/font-awesome.min.css",
				$dir."/css/layout.css",
				$dir."/css/typography.css"
			);
			Requirements::combine_files('Base.css', $BaseCSS);
			
			$BaseJS = array(
				$dir."/javascript/jquery-1.9.1.min.js",
				$dir."/javascript/scripts.js",
				$dir."/javascript/dropdowns.js",
				$dir."/javascript/responsive.js"
			);
			Requirements::combine_files('Base.js', $BaseJS);	

			if($this->owner->ParsedPageCSS())Requirements::combine_files($this->owner->ClassName.'.css', $this->owner->ParsedPageCSS());
			if($this->owner->ParsedPageJS())Requirements::combine_files($this->owner->ClassName.'.js', $this->owner->ParsedPageJS());
			if($this->owner->CustomJS())Requirements::customScript($this->owner->CustomJS()); 
			if($this->owner->ResponsiveCSS())Requirements::combine_files('Responsive.css', $this->owner->ResponsiveCSS());
		}
		
		function FindRedirectPage()
		{
			$URL_Full = $_SERVER['REQUEST_URI'];
			$URL_Parts = explode("?",$URL_Full);
			$URL_Path = $URL_Parts[0];
			// see fi there is a page with this URL as the redirect
			$Filters = "URLRedirects LIKE '%".$URL_Full."%' OR URLRedirects LIKE '%".$URL_Path."%'";
			$Results = DataList::create('Page')->where($Filters);
			if ($Results->Count())
			{
				if ($Results->Count() == 1) return $Results->First();
				foreach($Results as $Page)
				{
					// since our query used a wild card, check if we have a direct match to any page, return the first found
					$RedirectURLs = explode("\n",$Page->URLRedirects);
					if (in_array($URL_Full,$RedirectURLs) || in_array($URL_Path,$RedirectURLs)) return $Page;
				}
			}
		}
		
		function GetPossibleDirs()
		{
			$dirs = array();
			$dirs[] = "iq-basepages";
			$dirs[] = "iq-".strtolower($this->owner->ClassName);
			$dirs[] = "themes/mysite";		
			return $dirs;
		}
		
		function ParsedPageCSS()
		{
			$CssFiles = array();
			if ($files = $this->owner->PageCSS())
			{
				foreach($files as $file)
				{
					$CssFiles[$file] = $file;
				}
			}
			return $CssFiles;
		}
		
		function ParsedPageJS()
		{
			$JsFiles = array();
			if ($files = $this->owner->PageJS())
			{
				foreach($files as $file)
				{
					$JsFiles[$file] = $file;
				}
			}
			return $JsFiles;
		}
		
		function PageCSS()
		{			
			$CSSFiles = array();
			foreach($this->GetPossibleDirs() as $dir)
			{
				if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$dir."/css/pages/".$this->owner->ClassName.".css"))
				{
					$CSSFiles[] = $dir."/css/pages/".$this->owner->ClassName.".css";
				}
			}
	
			return $CSSFiles;
		}
		
		function ResponsiveCSS()
		{
			$CSSFiles = array();
			$CSSFiles[] = "themes/mysite/css/responsive.css";
			foreach($this->GetPossibleDirs() as $dir)
			{
				if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$dir."/css/pages/".$this->owner->ClassName."_responsive.css"))
				{
					$CSSFiles[] = $dir."/css/pages/".$this->owner->ClassName."_responsive.css";
				}
			}
	
			return $CSSFiles;
		}
		
		function PageJS()
		{
			$JSFiles = array();
			foreach($this->GetPossibleDirs() as $dir)
			{
				if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$dir."/javascript/pages/".$this->owner->ClassName.".js"))
				{
					$JSFiles[] = $dir."/javascript/pages/".$this->owner->ClassName.".js";
				}
			}
	
			return $JSFiles;
		}
		
		function CustomJS()
		{
			return null;
		}
		
		public function CopyrightYear()
		{
			return date("Y");
		}
		
		public function CopyrightName()
		{
			$arr_path = explode(".", $_SERVER['HTTP_HOST']);	
			return $arr_path[1].".".$arr_path[2];
		}
		
		public function NavNoFollow(){
			return $this->owner->NoFollow ? "rel='nofollow'" : "";	
		}
		
		public function ColAmount(){
			$i = 0;
			if($this->owner->LeftColumn)$i++;
			if($this->owner->CenterColumn)$i++;
			if($this->owner->RightColumn)$i++;
			return $i;
		}
		
		public function thanks()
		{
			return $this->owner->Customise(array());
		}
		
	}