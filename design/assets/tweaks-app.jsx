/* TLC Tweaks — three expressive dials that reshape the whole site's feel. */
const { useEffect } = React;

const DEFAULTS = /*EDITMODE-BEGIN*/{
  "mood": "editorial",
  "persona": "geometric",
  "density": "default"
}/*EDITMODE-END*/;

const MOODS = {
  noir:      { grayscale: "1",   brightness: "0.5",  contrast: "1.15", red: "#e80f03", paper: "#f4f3ef", ink: "#050505" },
  editorial: { grayscale: "0.4", brightness: "0.7",  contrast: "1.05", red: "#e80f03", paper: "#f4f3ef", ink: "#0a0a0a" },
  vivid:     { grayscale: "0",   brightness: "0.85", contrast: "1.1",  red: "#ff3b1f", paper: "#fff8ec", ink: "#100a08" },
};
const PERSONAS = {
  geometric: { display: "'Sora', sans-serif",           letter: "-0.04em" },
  editorial: { display: "'Instrument Serif', serif",    letter: "-0.02em" },
  brutalist: { display: "'JetBrains Mono', monospace",  letter: "-0.04em" },
};
const DENSITIES = {
  tight:    { padY: "60px",  scale: "0.9" },
  default:  { padY: "120px", scale: "1"   },
  spacious: { padY: "180px", scale: "1.15" },
};

const MOOD_OPTS = ['noir','editorial','vivid'];
const PERSONA_OPTS = ['geometric','editorial','brutalist'];
const DENSITY_OPTS = ['tight','default','spacious'];

function applyTweaks(t) {
  const root = document.documentElement;
  const m = MOODS[t.mood] || MOODS.editorial;
  const p = PERSONAS[t.persona] || PERSONAS.geometric;
  const d = DENSITIES[t.density] || DENSITIES.default;
  root.style.setProperty('--red', m.red);
  root.style.setProperty('--paper', m.paper);
  root.style.setProperty('--ink', m.ink);
  root.style.setProperty('--f-display', p.display);
  document.querySelectorAll('.hero__bg .tile img, .hero__bg .tile video').forEach(el => {
    el.style.filter = `grayscale(${m.grayscale}) brightness(${m.brightness}) contrast(${m.contrast})`;
  });
}

function App() {
  const [t, setTweak] = useTweaks(DEFAULTS);
  useEffect(() => { applyTweaks(t); }, [t.mood, t.persona, t.density]);

  return (
    <TweaksPanel label="Tweaks">
      <TweakSection label="Mood" />
      <TweakRadio label="Palette" value={t.mood} options={MOOD_OPTS} onChange={v => setTweak('mood', v)} />
      <TweakSection label="Type persona" />
      <TweakRadio label="Display font" value={t.persona} options={PERSONA_OPTS} onChange={v => setTweak('persona', v)} />
      <TweakSection label="Density" />
      <TweakRadio label="Breathing room" value={t.density} options={DENSITY_OPTS} onChange={v => setTweak('density', v)} />
    </TweaksPanel>
  );
}

const rootEl = document.getElementById('tweaks-root');
if (rootEl && window.React && window.ReactDOM && window.TweaksPanel) {
  ReactDOM.createRoot(rootEl).render(<App />);
} else {
  console.warn('[TLC] Tweaks deps missing', { React: !!window.React, ReactDOM: !!window.ReactDOM, TweaksPanel: !!window.TweaksPanel });
}

