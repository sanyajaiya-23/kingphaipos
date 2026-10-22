import sys
p=sys.argv[1]
s=open(p,encoding='utf8').read()
a=s.find('      <!-- วิดเจ็ตเวลาแบบ')
if a<0:
    a=s.find('      <div class="kp-wg"')
b=s.find('</script>\n',a)+len('</script>\n')
assert a>0 and b>a
new='''      <!-- วิดเจ็ตเวลาแบบการ์ดยาว (คำขอ 2026-10-21): นาฬิกาเข็ม + วันที่ไทย (พ.ศ.) + พยากรณ์อากาศ 3 ช่วง (Open-Meteo)
           เปลี่ยนพิกัดได้ที่ window.KP_WX = {lat:..,lng:..} ก่อนโหลดสคริปต์นี้ (ค่าเริ่มต้น กรุงเทพฯ) -->
      <div class="kp-wg" id="kpWg">
        <div class="kp-wg__clk"><svg viewBox="0 0 100 100" aria-hidden="true">
          <defs><linearGradient id="kwRim" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset=".45" stop-color="#ffc9e2"/><stop offset="1" stop-color="#b9a6ff"/></linearGradient>
          <radialGradient id="kwFace" cx=".4" cy=".35" r=".8"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#eceff6"/></radialGradient></defs>
          <circle cx="50" cy="50" r="48" fill="url(#kwRim)"/><circle cx="50" cy="50" r="48" fill="none" stroke="rgba(255,255,255,.9)" stroke-width="1.2"/>
          <circle cx="50" cy="50" r="40" fill="url(#kwFace)" stroke="rgba(120,134,170,.35)" stroke-width="1"/>
          <g id="kwTicks"></g>
          <circle cx="50" cy="50" r="9" fill="none" stroke="rgba(27,34,54,.35)" stroke-width=".8"/>
          <line id="kwHr" x1="50" y1="50" x2="50" y2="29" class="hh"/><line id="kwMn" x1="50" y1="50" x2="50" y2="17" class="mm"/>
          <line id="kwSc" x1="50" y1="60" x2="50" y2="13" class="ss"/><circle cx="50" cy="50" r="3.4" class="cd"/>
        </svg></div>
        <div class="kp-wg__inf">
          <small id="kwWd"></small>
          <b id="kwDate"></b>
          <div class="kp-wg__wx" id="kwWx">
            <div><span class="ic" id="wxI0"></span><b id="wxT0">--°</b><em>ตอนนี้</em></div>
            <div><span class="ic" id="wxI1"></span><b id="wxT1">--°</b><em id="wxL1">--:--</em></div>
            <div><span class="ic" id="wxI2"></span><b id="wxT2">--°</b><em id="wxL2">--:--</em></div>
          </div>
        </div>
      </div>
      <script>
      (function(){
        var DL=["อาทิตย์","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์"],
            ML=["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
        function $(i){return document.getElementById(i);}
        function p2(n){return (n<10?"0":"")+n;}
        function rot(el,deg){if(el)el.setAttribute("transform","rotate("+deg+" 50 50)");}
        function set(i,t){var e=$(i);if(e&&e.textContent!==t)e.textContent=t;}
        var g=$("kpTicks")||$("kwTicks");
        if(g&&!g.firstChild){var h="";for(var i=0;i<60;i++){var big=i%5===0,r1=big?31:34,r2=37.5,a=i*Math.PI/6/5;
          h+='<line x1="'+(50+Math.sin(a)*r1).toFixed(2)+'" y1="'+(50-Math.cos(a)*r1).toFixed(2)+'" x2="'+(50+Math.sin(a)*r2).toFixed(2)+'" y2="'+(50-Math.cos(a)*r2).toFixed(2)+'" stroke="#1b2236" stroke-width="'+(big?1.7:.6)+'" stroke-linecap="round"/>';}
          g.innerHTML=h;}
        function tick(){
          if(!$("kpWg"))return;
          var d=new Date(),hh=d.getHours(),m=d.getMinutes(),s=d.getSeconds();
          set("kwWd","วัน"+DL[d.getDay()]);
          set("kwDate",d.getDate()+" "+ML[d.getMonth()]+" "+(d.getFullYear()+543));
          rot($("kwHr"),(hh%12)*30+m*0.5);rot($("kwMn"),m*6+s*0.1);rot($("kwSc"),s*6);
        }
        tick();setInterval(function(){if(!document.hidden)tick();},1000);

        /* ---- พยากรณ์อากาศ ---- */
        var IC={
          sun:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
          part:'<circle cx="8" cy="8" r="3"/><path d="M8 1.5v1.2M1.5 8h1.2M3.4 3.4l.9.9M12.6 3.4l-.9.9"/><path d="M8 19h9a3.5 3.5 0 0 0 .4-7 5 5 0 0 0-9.4 1.2A3 3 0 0 0 8 19z"/>',
          cloud:'<path d="M7 19h10a4 4 0 0 0 .5-8 6 6 0 0 0-11.4 1.6A3.4 3.4 0 0 0 7 19z"/>',
          rain:'<path d="M7 14h10a4 4 0 0 0 .5-8 6 6 0 0 0-11.4 1.6A3.4 3.4 0 0 0 7 14z"/><path d="M8 17l-1 3M12 17l-1 3M16 17l-1 3"/>',
          storm:'<path d="M7 14h10a4 4 0 0 0 .5-8 6 6 0 0 0-11.4 1.6A3.4 3.4 0 0 0 7 14z"/><path d="M12.5 14l-2.5 4h3l-2 4"/>',
          fog:'<path d="M7 13h10a4 4 0 0 0 .5-8 6 6 0 0 0-11.4 1.6A3.4 3.4 0 0 0 7 13z"/><path d="M4 17h16M6 21h12"/>'
        };
        function kind(c){if(c===0)return"sun";if(c<=2)return"part";if(c===3)return"cloud";if(c===45||c===48)return"fog";if(c>=95)return"storm";if(c>=51)return"rain";return"cloud";}
        function ico(k){return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">'+IC[k]+'</svg>';}
        var loc=window.KP_WX||{lat:13.7563,lng:100.5018};
        function wx(){
          if(!window.fetch||!$("kwWx"))return;
          var u="https://api.open-meteo.com/v1/forecast?latitude="+loc.lat+"&longitude="+loc.lng+"&hourly=temperature_2m,weathercode&timezone=Asia%2FBangkok&forecast_days=2";
          fetch(u).then(function(r){return r.json();}).then(function(j){
            var T=j.hourly.time,n=new Date(),key=n.getFullYear()+"-"+p2(n.getMonth()+1)+"-"+p2(n.getDate())+"T"+p2(n.getHours())+":00",i=T.indexOf(key);
            if(i<0)return;
            for(var k=0;k<3;k++){
              var t=j.hourly.temperature_2m[i+k],c=j.hourly.weathercode[i+k];
              if(t==null)continue;
              $("wxI"+k).innerHTML=ico(kind(c));set("wxT"+k,Math.round(t)+"°C");
              if(k>0)set("wxL"+k,T[i+k].slice(11,16));
            }
            $("kwWx").classList.add("is-on");
          }).catch(function(){});
        }
        wx();setInterval(wx,30*60*1000);
      })();
      </script>
'''
s=s[:a]+new+s[b:]
open(p,'w',encoding='utf8').write(s)
print('patched')
