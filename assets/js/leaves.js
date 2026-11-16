/* QueueLess 2D leaf-particle animation (canvas) */
(function () {
  const cv = document.getElementById('leafCanvas');
  if (!cv) return;
  const cx = cv.getContext('2d');
  let leaves = [];
  function sizeCv() { cv.width = cv.offsetWidth; cv.height = cv.offsetHeight; }
  window.addEventListener('resize', sizeCv); sizeCv();
  for (let i = 0; i < 26; i++) {
    leaves.push({
      x: Math.random() * cv.width, y: Math.random() * cv.height,
      s: 6 + Math.random() * 10, r: Math.random() * Math.PI * 2,
      vr: (Math.random() - .5) * .02, vy: .25 + Math.random() * .5,
      vx: .2 + Math.random() * .4, sw: Math.random() * Math.PI * 2,
      hue: 120 + Math.random() * 40
    });
  }
  (function anim() {
    cx.clearRect(0, 0, cv.width, cv.height);
    leaves.forEach(l => {
      l.y += l.vy; l.x += Math.sin(l.sw += .015) * l.vx * .6; l.r += l.vr;
      if (l.y > cv.height + 20) { l.y = -20; l.x = Math.random() * cv.width; }
      cx.save(); cx.translate(l.x, l.y); cx.rotate(l.r);
      cx.fillStyle = 'hsla(' + l.hue + ',60%,45%,.35)';
      cx.beginPath(); cx.moveTo(0, -l.s);
      cx.quadraticCurveTo(l.s * .8, 0, 0, l.s);
      cx.quadraticCurveTo(-l.s * .8, 0, 0, -l.s);
      cx.fill(); cx.restore();
    });
    requestAnimationFrame(anim);
  })();
})();
