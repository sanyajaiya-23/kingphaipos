import sys
p=sys.argv[1]
s=open(p,encoding='utf8').read()
if 'kwDs' in s: print('already'); sys.exit()
a='          <small id="kwWd"></small>\n'
assert a in s
s=s.replace(a,a+'          <div class="kp-wg__mini"><b id="kwT">--:--</b><span id="kwDs"></span></div>\n',1)
b='          set("kwDate",d.getDate()+" "+ML[d.getMonth()]+" "+(d.getFullYear()+543));\n'
assert b in s
s=s.replace(b,b+'          set("kwT",p2(hh)+":"+p2(m));set("kwDs","วัน"+DL[d.getDay()].slice(0,3)+" "+d.getDate()+" "+MSH[d.getMonth()]);\n',1)
c='        function $(i){return document.getElementById(i);}\n'
i=s.find(c, s.find('kpWg'))
s=s[:i]+'        var MSH=["ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];\n'+s[i:]
open(p,'w',encoding='utf8').write(s); print('patched')
