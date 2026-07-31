'use client'

import { useEffect, useMemo, useState } from 'react'
import { LinearFilter, SRGBColorSpace, Texture, TextureLoader, VideoTexture } from 'three'
import { acquire, release, whenEvicted } from './videoBudget'
import type { DeviceTier } from './useDeviceTier'

/**
 * A texture for a scene: video where the device can afford it, poster where it
 * cannot.
 *
 * On the reduced and off tiers no <video> element is created at all — not
 * created-and-paused, but never constructed, so a low-end phone never pays the
 * decoder setup cost.
 *
 * On the full tier the element is gated by the site-wide two-decode budget and
 * by an IntersectionObserver, so a stream only runs while its section is on
 * screen and only if a slot is free.
 */
export function useSceneTexture({
  id,
  videoUrl,
  posterUrl,
  tier,
  track,
}: {
  id: string
  videoUrl: string | null
  posterUrl: string | null
  tier: DeviceTier
  /** The DOM element whose visibility gates playback. */
  track?: Element | null
}): { texture: Texture | null; isVideo: boolean } {
  const [texture, setTexture] = useState<Texture | null>(null)

  const wantsVideo = tier === 'full' && !!videoUrl

  // Poster path — also the fallback while a video buffers.
  useEffect(() => {
    if (wantsVideo || !posterUrl) return

    let cancelled = false
    const loader = new TextureLoader()
    loader.setCrossOrigin('anonymous')

    loader.load(posterUrl, (loaded) => {
      if (cancelled) {
        loaded.dispose()
        return
      }
      loaded.colorSpace = SRGBColorSpace
      setTexture(loaded)
    })

    return () => {
      cancelled = true
    }
  }, [wantsVideo, posterUrl])

  /**
   * Built synchronously rather than in an effect: constructing the element and
   * its texture is derivation, not a side effect, and setting state from an
   * effect for it would render once with no texture and then again with one.
   * The effect below owns playback and teardown only.
   */
  const video = useMemo(() => {
    if (!wantsVideo || !videoUrl || typeof document === 'undefined') return null

    const element = document.createElement('video')
    element.src = videoUrl
    element.muted = true
    element.loop = true
    element.playsInline = true
    element.crossOrigin = 'anonymous'
    element.preload = 'none'

    return element
  }, [wantsVideo, videoUrl])

  const videoTexture = useMemo(() => {
    if (!video) return null

    const created = new VideoTexture(video)
    created.minFilter = LinearFilter
    created.magFilter = LinearFilter
    created.colorSpace = SRGBColorSpace

    return created
  }, [video])

  // Playback and teardown.
  useEffect(() => {
    if (!video || !videoTexture) return

    const pause = () => video.pause()
    whenEvicted(id, pause)

    const start = () => {
      if (!acquire(id)) return
      // play() rejects if the element is detached or autoplay is blocked;
      // neither is worth an unhandled rejection in the console.
      void video.play().catch(() => {})
    }

    const stop = () => {
      video.pause()
      release(id)
    }

    let observer: IntersectionObserver | undefined

    if (track) {
      observer = new IntersectionObserver(
        ([entry]) => (entry.isIntersecting ? start() : stop()),
        { rootMargin: '10% 0px' }
      )
      observer.observe(track)
    } else {
      start()
    }

    return () => {
      observer?.disconnect()
      video.pause()
      release(id)
      // Detaching alone leaves the decoder running on some browsers; clearing
      // the source and reloading is what actually frees it.
      video.removeAttribute('src')
      video.load()
      videoTexture.dispose()
    }
  }, [video, videoTexture, id, track])

  return useMemo(
    () => ({ texture: videoTexture ?? texture, isVideo: wantsVideo }),
    [videoTexture, texture, wantsVideo]
  )
}
