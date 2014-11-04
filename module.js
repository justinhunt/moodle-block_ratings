// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JavaScript library for the Ratings Block.
 *
 * @package    mod
 * @subpackage quizletimport
 * @copyright  2014 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.block_ratings = M.block_ratings || {};


M.block_ratings.helper = {
    currentassig: null,
	yuiobj: null,
	headercontent: '',
	panels:  Array(),
	
    /**
     * @param Y the YUI object
     * @param start, the timer starting time, in seconds.
     * @param preview, is this a quiz preview?
     */
    makepanel: function(Y, opts) {
    	if(this.yuiobj ==null){
    		this.yuiobj = Y;
    	}
    	
   		var panel = new Y.Panel({
			srcNode      : '.' + opts['panelclass'],
			headerContent: opts['headercontent'],
			width        : opts['width'],
			height		 : opts['height'],
			zIndex       : 5,
			centered     : true,
			modal        : false,
			visible      : false,
			render       : true,
			plugins      : [Y.Plugin.Drag]
		});
		
		this.panels[opts['panelid']] = panel; 
		this.headercontent = opts['headercontent'];
		
		//make our contents block visible
		var cb = Y.one('.block_ratings-form-container');
		if(cb){
			cb.set('style.display','block');
		}
 
		
		//if we wish to show a dialog on load, there will be data in currentassig
		//if no data, we better do an ajax check for a recently completed
		if(opts['currentassig']!=null){
			this.showpanel(opts['panelid'], opts['currentassig']);
		}else{
			this.fetchrecentcomplete(opts);
		}
    },
    
    fetchrecentcomplete: function(opts){
    	//we need to add rateable here like this, but its an array..?
    	//'&rateable=' + opts['rateable'] +
    	var uri = M.cfg.wwwroot + '/blocks/ratings/ajaxratings.php' +
    		'?action=fetchrecentcomplete' +
    		'&courseid=' + opts['courseid'] +
    		'&panelid=' + opts['panelid'] +
    		'&ratearea=' + opts['ratearea'] +
    		'&parentmode=' + opts['parentmode'] +
			'&sesskey=' +  M.cfg.sesskey;
		this.yuiobj.on('io:complete', M.block_ratings.helper.iocomplete, this.yuiobj,null);	
        this.yuiobj.io(uri);
    },
    
    // Define a function to handle the AJAX response.
    iocomplete: function(id,o,args) {
    	var id = id; // Transaction ID.
        var returndata = o.responseText; // Response data.
       //console.log(returndata);
       var Y = M.block_ratings.helper.yuiobj;
       var result = Y.JSON.parse(returndata);
       if(result.action=='update'){
			return;       
       }else{
			var currentassig = result.currentassig;
			var panelid = result.panelid;
			if(currentassig){
				M.block_ratings.helper.showpanel(panelid, currentassig);
			}
        }

    },
    
    showpanel: function(panelid,currentassig){
    	var brh = M.block_ratings.helper;
    	brh.currentassig= brh.yuiobj.JSON.parse(currentassig);
    	brh.panels[panelid].set('headerContent',brh.headercontent.replace('\{\$a\}', brh.currentassig.activityname));
    	brh.panels[panelid].show();
    },
    
    hidepanel: function(panelid){
    	this.panels[panelid].hide();
    },
    
    updateratingimage: function(rating){
    	$id = 'block_ratings-item-' + this.currentassig.ratearea + '-' + this.currentassig.activityid;
    	var img = this.yuiobj.one('#' + $id);
    	if(img){
    		img.setAttribute('src',M.cfg.wwwroot + '/blocks/ratings/pix/' + this.currentassig.ratearea + '0' + rating + '.png');
    	}else{
    		//possibly no image, cos completiondata recieved via ajax
    		//console.log('nup' + $id);
    	}
    },
    
    sendmessage: function(panelid, rating){
    	var uri = M.cfg.wwwroot + '/blocks/ratings/ajaxratings.php' +
    		'?action=update' +
    		'&courseid=' + this.currentassig.courseid +
    		'&activityid=' + this.currentassig.activityid +
    		'&itemid=' + this.currentassig.itemid +
    		'&ratearea=' + this.currentassig.ratearea +
			'&heading=' + this.currentassig.ratearea +
			'&sesskey=' +  M.cfg.sesskey  +
    		'&rating=' + rating;

        this.yuiobj.io(uri);
        this.hidepanel(panelid);
        this.updateratingimage(rating);
    }

}; 
