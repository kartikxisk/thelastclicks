"use client";

import { 
  BiCameraMovie, 
  BiRefresh, 
  BiLock,
  BiTargetLock 
} from "react-icons/bi";
import { 
  BsLightning, 
  BsBuilding, 
  BsBox, 
  BsCameraVideo, 
  BsMusicNoteBeamed, 
  BsTv, 
  BsFilm, 
  BsHeadphones,
  BsPersonBoundingBox,
  BsCamera,
  BsLightbulb,
  BsMic,
  BsGear
} from "react-icons/bs";
import { 
  HiSparkles, 
  HiOutlineStar,
  HiOutlinePhotograph,
  HiOutlineUserGroup
} from "react-icons/hi";
import { 
  IoColorPaletteOutline, 
  IoDiamondOutline,
  IoRocketOutline,
  IoImageOutline
} from "react-icons/io5";
import { 
  MdCelebration, 
  MdOutlineMovie, 
  MdOutlineTheaters,
  MdOutlineHandshake,
  MdCheckCircle
} from "react-icons/md";
import { 
  TbSparkles,
  TbDrone
} from "react-icons/tb";
import { 
  GiDiamondRing, 
  GiLargeDress,
  GiFilmStrip
} from "react-icons/gi";
import { 
  FiCamera,
  FiVideo,
  FiEdit3,
  FiUser,
  FiImage
} from "react-icons/fi";
import { 
  RiUserStarLine 
} from "react-icons/ri";

// Icon map for dynamic icon rendering
export const iconMap: Record<string, React.ComponentType<{ className?: string }>> = {
  // Photography icons
  GiDiamondRing,
  BsPersonBoundingBox,
  MdCelebration,
  BsBox,
  GiLargeDress,
  BsBuilding,
  
  // Videography icons
  BiCameraMovie,
  BsCameraVideo,
  BsMusicNoteBeamed,
  MdOutlineMovie,
  BsTv,
  MdOutlineTheaters,
  
  // Editing icons
  HiSparkles,
  BsFilm,
  IoColorPaletteOutline,
  TbSparkles,
  BsHeadphones,
  HiOutlineStar,
  
  // Feature icons
  BsLightning,
  BiRefresh,
  IoDiamondOutline,
  BiLock,
  BiTargetLock,
  BsLightbulb,
  MdOutlineHandshake,
  
  // General icons
  FiCamera,
  FiVideo,
  FiEdit3,
  FiUser,
  FiImage,
  BsCamera,
  BsMic,
  BsGear,
  TbDrone,
  GiFilmStrip,
  IoRocketOutline,
  IoImageOutline,
  HiOutlinePhotograph,
  HiOutlineUserGroup,
  RiUserStarLine,
  MdCheckCircle,
};

interface IconProps {
  readonly name: string;
  readonly className?: string;
}

export default function Icon({ name, className = "w-6 h-6" }: IconProps) {
  const IconComponent = iconMap[name];
  
  if (!IconComponent) {
    return null;
  }
  
  return <IconComponent className={className} />;
}
