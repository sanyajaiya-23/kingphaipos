import sys,re
p=sys.argv[1]
s=open(p,encoding='utf8').read()
if 'kp-wg' in s:
    print('already'); sys.exit()
anchor='      <div class="np-fd"><b id="npSwDate"></b><span id="npSwDow"></span></div>\n'
assert anchor in s
html=anchor+'''      <!-- วิดเจ็ตเวลาแบบ 4 ช่อง (คำขอ 2026-10-21): ช่องเวลาไล่สีฟ้า + นาฬิกาเข็ม + ช่องวันที่ + แถบเวลา/วันที่ — อัปเดตด้วยสคริปต์ด้านล่าง -->
      <div class="kp-wg" id="kpWg">
        <div class="kp-wg__a"><div class="kp-wg__ad"><span id="kwD"></span><i id="kwW"></i></div><b id="kwH">--</b><b id="kwM">--</b></div>
        <div class="kp-wg__b"><svg viewBox="0 0 100 100" aria-hidden="true"><circle cx="50" cy="50" r="46" class="rg"/><circle cx="50" cy="50" r="27" class="fc"/><line id="kwHr" x1="50" y1="50" x2="50" y2="30" class="hh"/><line id="kwMn" x1="50" y1="50" x2="50" y2="20" class="mm"/><line id="kwSc" x1="50" y1="58" x2="50" y2="14" class="ss"/><circle cx="50" cy="50" r="3.2" class="cd"/></svg></div>
        <div class="kp-wg__c"><small id="kwWd"></small><b id="kwDn"></b></div>
        <div class="kp-wg__d"><span class="t" id="kwT">--:--</span><span class="s"></span><span class="dd"><em id="kwDm"></em><i id="kwW2"></i></span></div>
      </div>
      <script>
      (function(){
        var DS=["อา","จ","อ","พ","พฤ","ศ","ส"],DL=["วันอาทิตย์","วันจันทร์","วันอังคาร","วันพุธ","วันพฤหัส","วันศุกร์","วันเสาร์"],
            MS=["ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];
        function $(i){return document.getElementById(i);}
        function p2(n){return (n<10?"0":"")+n;}
        function rot(el,deg){if(el)el.setAttribute("transform","rotate("+deg+" 50 50)");}
        function set(i,t){var e=$(i);if(e&&e.textContent!==t)e.textContent=t;}
        function tick(){
          if(!$("kpWg"))return;
          var d=new Date(),h=d.getHours(),m=d.getMinutes(),s=d.getSeconds(),w=d.getDay();
          set("kwD",d.getDate()+" "+MS[d.getMonth()]);set("kwW",DS[w]);
          set("kwH",p2(h));set("kwM",p2(m));
          set("kwWd",DL[w]);set("kwDn",String(d.getDate()));
          set("kwT",p2(h)+":"+p2(m));set("kwDm",d.getDate()+"/"+(d.getMonth()+1));set("kwW2",DS[w]);
          rot($("kwHr"),(h%12)*30+m*0.5);rot($("kwMn"),m*6+s*0.1);rot($("kwSc"),s*6);
        }
        tick();setInterval(function(){if(!document.hidden)tick();},1000);
      })();
      </script>
'''
s=s.replace(anchor,html,1)
open(p,'w',encoding='utf8').write(s)
print('patched')
