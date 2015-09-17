// JavaScript Document
window._isDisableFlipPage = false;
	window.pageScroll = {
		disable : function () {
			window._isDisableFlipPage = true;
		},
		enable : function () {
			window._isDisableFlipPage = false;
		}
	};

function PageXp(box,content,dir){
	this.box=box;
	this.content=content;
	//this.dir=dir;// front : back 
	//初始化
	this.pages = this.content.find('.page');
	this.pages.height(window.innerHeight);
	this.pages.first().addClass('z-actived');
					
		//定义临时变量
	this.currentPage = null;
	this.activePage = null;
	this.triggerLoop = false;
	this.startX = 0;
	this.startY = 0;
	this.moveDistanceX = 0;
	this.moveDistanceY = 0;
	this.isStart = false;
	this.isNext = false;
	this.isFirstTime = true;				
	this.startoff=false;
	this.isStopEvent=false;
	this.addEventBox();

	
}

PageXp.prototype.addEventBox=function(){
	this.box.addEventListener('touchstart',touchDown, false);
	this.box.addEventListener('touchmove',touchMove, false);				
	this.box.addEventListener('touchend',touchEnd, false);	
	this.box.addEventListener('mousedown',touchDown, false);
	this.box.addEventListener('mousemove',touchMove, false);				
	this.box.addEventListener('mouseup',touchEnd, false);
	
	var that=this;
	
		function touchDown(ev) {
			if(that.isStopEvent){
				return ;
				}
			
			//动画正在运行时或禁止翻页时不进行下一轮切换
			if(window._isDisableFlipPage){
				return;
			}
			//获取当前显示的页面和将要显示的页面
			that.currentPage = that.pages.filter('.z-actived').get(0);
			that.activePage = null;
			//初始化切换变量、属性
			if(that.currentPage){
				that.isStart = true;
				that.isNext = false;
				that.isFirstTime = true;
				that.moveDistanceX = 0;
				that.moveDistanceY = 0;
				 if (ev.hasOwnProperty('changedTouches')) {
					 that.startX = ev.changedTouches[0].pageX
					 that.startY = ev.changedTouches[0].pageY
					 }else{
						that.startX = ev.pageX;
						that.startY = ev.pageY; 				 
						 }
				that.currentPage.classList.add('z-move');
				that.currentPage.style.webkitTransition = 'none';
			}
		}
	
		
		function touchMove(ev) {
				if(that.isStopEvent){
					return ;
					}
						//当启动新一轮切换并且将要显示的页面不为null或者为启动后第一次进入move事件
						if(that.isStart && (that.activePage || that.isFirstTime)){
							//获取移动距离
							 if (ev.hasOwnProperty('changedTouches')) {
								that.moveDistanceX = event.changedTouches[0].pageX - that.startX;
								that.moveDistanceY = event.changedTouches[0].pageY - that.startY;
								 }else{
									that.moveDistanceX = event.pageX - that.startX;
									that.moveDistanceY = event.pageY - that.startY;
									 }
							
							//如果Y移动的距离大于X移动的距离，则进行翻页操作
							if(Math.abs(that.moveDistanceY) > Math.abs(that.moveDistanceX)){
								
								//判断用户是向上还是向下拉
								if(that.moveDistanceY > 0){
									//向下拉：显示上一页
									if(that.isNext || that.isFirstTime){
										//设置临时变量值
										that.isNext = false;
										that.isFirstTime = false;
										
										//清除上次将要显示的页面
										if(that.activePage){
											that.activePage.classList.remove('z-active');
											that.activePage.classList.remove('z-move');
										}
										//获取当前将要显示的上一页
										if(that.currentPage.previousElementSibling && that.currentPage.previousElementSibling.classList.contains('page')){
											that.activePage = that.currentPage.previousElementSibling;
										} else {
											
											
											
											if(that.triggerLoop) {
												that.activePage = that.pages.last().get(0);
											} else {
												that.activePage = false;
											}
										}
										if(that.activePage && that.activePage.classList.contains('page')){
											//获取成功：初始化上一页
											that.activePage.classList.add('z-active')
											that.activePage.classList.add('z-move');
											that.activePage.style.webkitTransition = 'none';
											that.activePage.style.webkitTransform = 'translateY(-100%)';
											$(that.activePage).trigger('active');
										}else{
											//获取失败：重置当前页
											that.currentPage.style.webkitTransform = 'translateY(0px)';
											that.activePage = null;
										}
									}else{
										//移动时设置样式
										that.currentPage.style.webkitTransform = 'translateY('+ (that.moveDistanceY) +'px)';
										that.activePage.style.webkitTransform = 'translateY(-'+ (window.innerHeight - that.moveDistanceY) +'px)';
									}
									
									
								}else if(that.moveDistanceY < 0){
									//向上拉：显示下一页
									
									if(!that.isNext || that.isFirstTime){
										//设置临时变量值
										that.isNext = true;
										that.isFirstTime = false;
										//清除上次将要显示的页面
										if(that.activePage){
											that.activePage.classList.remove('z-active');
											that.activePage.classList.remove('z-move');
										}
										
										
										//获取当前将要显示的下一页
										if(that.currentPage.nextElementSibling && that.currentPage.nextElementSibling.classList.contains('page')) {
											that.activePage =  that.currentPage.nextElementSibling;
										} else {
											
											
											//activePage =  $pages.first().get(0);
											//triggerLoop = true;
										}
										if(that.activePage && that.activePage.classList.contains('page')){
											//获取成功：初始化下一页
											that.activePage.classList.add('z-active');
											that.activePage.classList.add('z-move');
											that.activePage.style.webkitTransition = 'none';
											that.activePage.style.webkitTransform = 'translateY('+window.innerHeight+'px)';
											$(that.activePage).trigger('active');
										}else{
											//获取失败：重置当前页
											that.currentPage.style.webkitTransform = 'translateY(0px)';
											that.activePage = null;
										}
									}else{
										//移动时设置样式
										//document.title=that.moveDistanceY/window.innerHeight;
										
										that.currentPage.style.webkitTransform = 'translateY('+ (that.moveDistanceY) +'px)';
										that.activePage.style.webkitTransform = 'translateY('+ (window.innerHeight + that.moveDistanceY) +'px)';
									}
								}
							}
						}
					}	


		function touchEnd(e) {
			if(that.isStopEvent){
				return ;
				}
			
					if(that.isStart){
							
							//设置临时变量
							that.isStart = false;
							if(that.activePage){
								window._isDisableFlipPage = true;
								//启动转场动画
								that.currentPage.style.webkitTransition = '-webkit-transform 0.4s ease-out';
								that.activePage.style.webkitTransition = '-webkit-transform 0.4s ease-out';
								//判断移动距离是否超过100
								if(Math.abs(that.moveDistanceY) > Math.abs(that.moveDistanceX) && Math.abs(that.moveDistanceY) > 100){
									//切换成功：设置当前页面动画
									var index = that.pages.index(that.activePage);
									if(that.isNext){
										
										val++;	
										
										if(val>=4){
											$("#arr").hide();
											}else{
												$("#arr").show().attr('class','arr01');
												}	
										
										that.currentPage.style.webkitTransform = 'translateY(-'+ window.innerHeight +'px)';
										that.activePage.style.webkitTransform = 'translateY(0px)';
									}else{
										
										val--;
										
										if(val==0){
											$("#arr").show().attr('class','arr');
											}else if(val>=4){
												$("#arr").hide();
												}else{
													$("#arr").show().attr('class','arr01');
													}
									
										
										that.currentPage.style.webkitTransform = 'translateY('+ window.innerHeight +'px)';
										that.activePage.style.webkitTransform = 'translateY(0px)';
									}
									that.pages.eq(index).addClass('active').siblings('.page').removeClass('active')
									//页面动画运行完成后处理
									setTimeout(function () {
										that.activePage.classList.remove('z-active');
										that.activePage.classList.remove('z-move');
										that.activePage.classList.add('z-actived');
										that.currentPage.classList.remove('z-actived');
										that.currentPage.classList.remove('z-move');
										window._isDisableFlipPage = false;
										//保存当前页面，并触发页面事件
										$(that.activePage).trigger('current');
										$(that.currentPage).trigger('hide');
									}, 600);
								}else{
									//切换取消：设置当前页面动画
									if(that.isNext){
										that.currentPage.style.webkitTransform = 'translateY(0%)';
										that.activePage.style.webkitTransform = 'translateY(100%)';
									}else{
										
										that.currentPage.style.webkitTransform = 'translateY(0%)';
										that.activePage.style.webkitTransform = 'translateY(-100%)';
									}
									//页面动画运行完成后处理
									setTimeout(function () {
										that.activePage.classList.remove('z-active');
										that.activePage.classList.remove('z-move');
										window._isDisableFlipPage = false;
									}, 600);
								}
							}else{
								that.currentPage.classList.remove('z-move');
							}
						}
		}	
	
	
	
	
	}


