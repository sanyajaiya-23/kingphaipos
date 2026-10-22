import sys
p=sys.argv[1]
s=open(p,encoding='utf8').read()
if 'kpSwAuto' in s: print('already'); sys.exit()
a='      <span class="np-sw__foldbadge" id="npSwFoldBadge" aria-hidden="true"></span>\n    </button>\n  </div>\n'
assert a in s, 'anchor'
js='''  <script>
  /* วิดเจ็ตเริ่มต้นแบบย่อ (คำขอ 2026-10-21): แตะที่นาฬิกา -> ปุ่มแผ่ออกนุ่มๆ / ไม่มีการกดต่อ 2 วินาที -> ยุบกลับเอง (kpSwAuto) */
  (function kpSwAuto(){
    var w=document.getElementById("npSideWidget"),f=document.getElementById("npSwFold");
    if(!w||!f)return;
    var IDLE=2000,CLOSE=240,t=null,closing=false;
    w.classList.add("np-sw--folded");f.setAttribute("aria-expanded","false");f.setAttribute("aria-label","เปิดปุ่ม");
    function open(){return !w.classList.contains("np-sw--folded");}
    function listOpen(){var m=document.getElementById("mapControls");return !!(m&&m.classList.contains("is-open"));}
    function clear(){if(t){clearTimeout(t);t=null;}}
    function arm(){clear();if(!open())return;t=setTimeout(function(){if(listOpen()){arm();return;}collapse();},IDLE);}
    function collapse(){
      clear();if(!open()||closing)return;closing=true;w.classList.add("np-sw--closing");
      setTimeout(function(){w.classList.remove("np-sw--closing");closing=false;if(open())f.click();},CLOSE);
    }
    ["pointerdown","touchstart","keydown","click"].forEach(function(ev){w.addEventListener(ev,function(){if(open()&&!closing)arm();},true);});
    try{new MutationObserver(function(){if(open())arm();else clear();}).observe(w,{attributes:true,attributeFilter:["class"]});}catch(e){}
    var card=w.querySelector(".kp-wg");
    if(card)card.addEventListener("click",function(){if(open())collapse();});
  })();
  </script>
'''
s=s.replace(a,a+js,1)
open(p,'w',encoding='utf8').write(s); print('patched')
