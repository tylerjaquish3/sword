import{d as y,s as g}from"./sync-manager-DugwGLO8.js";import{e as v}from"./empty-state-Cgc_LYLQ.js";function h(n){const t=String(n.getMonth()+1).padStart(2,"0"),a=String(n.getDate()).padStart(2,"0"),s=n.getFullYear();return`${t}/${a}/${s}`}function b(n){const[t,a,s]=n.split("/").map(Number),c=new Date(s,t-1,a),l=c.toLocaleDateString("en-US",{weekday:"long"}),e=c.toLocaleDateString("en-US",{month:"short"});return`${l}, ${e} ${a}, ${s}`}function u(n){const[t,a,s]=n.split("/").map(Number);return s*1e4+t*100+a}async function f(){const[n,t,a]=await Promise.all([y.getAll("translations"),y.getAll("prayers"),y.getAll("prayerTypes")]);if(n.length===0){v.showEmptyState();return}document.getElementById("offline-reader-content").classList.remove("d-none");const s=new Map(a.map(e=>[e.id,e.name])),c=document.getElementById("or-prayer-type");a.forEach(e=>{const r=document.createElement("option");r.value=e.id,r.textContent=e.name,c.appendChild(r)});function l(){const e=document.getElementById("or-prayer-list"),r=document.getElementById("or-prayer-subtitle");if(t.length===0){r.textContent="No entries yet",e.innerHTML=`
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="mdi mdi-heart-outline mdi-48px mb-3 d-block" style="color: var(--sword-gold);"></i>
                            <h5 class="text-muted mb-1">No prayers recorded yet</h5>
                            <p class="text-muted small">Add one below.</p>
                        </div>
                    </div>
                </div>`;return}r.textContent=`${t.length} ${t.length===1?"prayer":"prayers"} recorded`;const d=new Map;t.forEach(o=>{d.has(o.date)||d.set(o.date,[]),d.get(o.date).push(o)});const i=Array.from(d.keys()).sort((o,m)=>u(m)-u(o));e.innerHTML=i.map(o=>{const m=d.get(o).map(p=>`
                    <div class="mb-3">
                        <div class="prayer-type-header">
                            <span class="prayer-type-dot"></span>
                            <span class="prayer-type-label">${s.get(p.prayer_type_id)||"Prayer"}</span>
                        </div>
                        <p class="text-muted mb-0 ps-3" style="font-size: 0.875rem; line-height: 1.6;">${p.content}</p>
                    </div>`).join("");return`
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h4 class="card-title mb-0">${b(o)}</h4>
                            </div>
                            <div class="reading-section-divider mt-0 mb-3"></div>
                            ${m}
                        </div>
                    </div>
                </div>`}).join("")}l(),document.getElementById("or-save-prayer").addEventListener("click",async()=>{const e=document.getElementById("or-prayer-content").value.trim();if(!e)return;const r=Number(c.value),d=h(new Date);await g.queue("prayer",{date:d,[`type${r}`]:e});const i={id:`local-${crypto.randomUUID()}`,date:d,content:e,prayer_type_id:r};t.push(i),await y.putAll("prayers",[i]),document.getElementById("or-prayer-content").value="",document.getElementById("or-sync-status").textContent="Prayer queued — will sync once you're back online.",l()})}document.addEventListener("DOMContentLoaded",f);
