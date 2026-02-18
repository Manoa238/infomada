// === THREE.js ===
const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(
  75,
  window.innerWidth / window.innerHeight,
  0.1,
  1000
);
const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.setClearColor(0x000000, 0);
document.getElementById('scene-container').appendChild(renderer.domElement);

const geometry = new THREE.TorusKnotGeometry(1, 0.3, 100, 16);
const material = new THREE.MeshStandardMaterial({
  color: 0x00ffff,
  roughness: 0.2,
  metalness: 0.8,
});
const torusKnot = new THREE.Mesh(geometry, material);
scene.add(torusKnot);

camera.position.z = 4;

scene.add(new THREE.AmbientLight(0x404040));
const pointLight = new THREE.PointLight(0xffffff, 1);
pointLight.position.set(5, 5, 5);
scene.add(pointLight);

// Lost man GIF
const loader = new THREE.TextureLoader();
loader.load(
  'https://i.pinimg.com/originals/e8/a1/17/e8a1179c287371815a8250f100e91372.gif',
  function (texture) {
    const planeGeometry = new THREE.PlaneGeometry(1, 1);
    const planeMaterial = new THREE.MeshBasicMaterial({
      map: texture,
      transparent: true,
    });
    const lostManPlane = new THREE.Mesh(planeGeometry, planeMaterial);
    lostManPlane.scale.set(0.5, 0.5, 0.5);
    lostManPlane.position.set(0, 1, 0);
    scene.add(lostManPlane);

    function animateLostMan() {
      lostManPlane.rotation.y += 0.01;
      requestAnimationFrame(animateLostMan);
      renderer.render(scene, camera);
    }
    animateLostMan();
  }
);

function animate() {
  requestAnimationFrame(animate);
  torusKnot.rotation.x += 0.01;
  torusKnot.rotation.y += 0.01;
  renderer.render(scene, camera);
}
animate();

window.addEventListener('resize', () => {
  camera.aspect = window.innerWidth / window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight);
});

// === particles.js ===
particlesJS('scene-container', {
  particles: {
    number: { value: 40, density: { enable: true, value_area: 800 } },
    color: { value: '#00ffff' },
    shape: {
      type: 'circle',
      stroke: { width: 0, color: '#000000' },
      polygon: { nb_sides: 5 },
    },
    opacity: {
      value: 0.5,
      random: true,
      anim: { enable: false },
    },
    size: {
      value: 2,
      random: true,
      anim: { enable: false },
    },
    line_linked: {
      enable: true,
      distance: 150,
      color: '#00ffff',
      opacity: 0.4,
      width: 1,
    },
    move: {
      enable: true,
      speed: 2,
      direction: 'none',
      random: true,
      out_mode: 'out',
    },
  },
  interactivity: {
    detect_on: 'canvas',
    events: {
      onhover: { enable: true, mode: 'grab' },
      onclick: { enable: true, mode: 'push' },
    },
    modes: {
      grab: { distance: 140, line_linked: { opacity: 1 } },
      push: { particles_nb: 4 },
    },
  },
  retina_detect: true,
});
