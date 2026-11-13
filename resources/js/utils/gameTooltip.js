import { translate } from './translate';

// Game tooltip markup (heroes_data_abilities / heroes_data_talents raw_description) → paragraphs of styled segments.
// Numbers followed by <c val="#ColorGray">~~rate~~</c> scale with hero level.

const COLOUR_CLASSES = {
  '#TooltipNumbers': 'font-bold text-white',
  '#ColorGray': 'text-gray-medium',
  '#TooltipQuest': 'text-quest',
  '#TooltipGreen': 'text-green',
  '#AbilityPassive': 'text-green',
  '#ColorViolet': 'text-purple',
};

const STYLE_CLASSES = {
  StandardTooltipHeader: 'font-bold',
  TooltipSubscript: 'text-sm text-gray-medium',
};

const TOKEN = /<(\/?)(c|s|n|img)\b([^>]*?)\/?>/g;

// The game stores the rate in 1/4096 fixed point, so 0.04 is really 0.0400390625.
export function scaleValue(base, rate, level){
  const rateQ = Math.round(rate * 4096) / 4096;
  return base * Math.pow(1 + rateQ, level);
}

function roundLike(value, baseText){
  const decimals = (baseText.split('.')[1] || '').length;
  const factor = Math.pow(10, decimals);
  return (Math.floor(value * factor + 0.5) / factor).toFixed(decimals);
}

function formatRate(rate){
  return translate('+:rate% per level', { rate: parseFloat((rate * 100).toFixed(2)) });
}

function tokenize(raw){
  const segments = [];
  const classStack = [];
  let last = 0;
  let match;

  const pushText = (text) => {
    // Muradin's Storm Bolt has a stray "<" before a tag
    text = text.replace(/<$/, '');
    if(text){
      segments.push({ text, class: classStack.filter(c => c).join(' '), number: classStack.includes(COLOUR_CLASSES['#TooltipNumbers']) });
    }
  };

  TOKEN.lastIndex = 0;
  while((match = TOKEN.exec(raw)) !== null){
    pushText(raw.slice(last, match.index));
    last = TOKEN.lastIndex;
    const [, closing, tag, attrs] = match;
    const val = (attrs.match(/val="([^"]*)"/) || [])[1];

    if(tag === 'n'){
      segments.push({ break: true });
    }else if(tag === 'img'){
      if(/Quest/i.test(attrs)){
        segments.push({ questIcon: true });
      }
    }else if(closing){
      classStack.pop();
    }else{
      classStack.push(tag === 'c' ? (COLOUR_CLASSES[val] || '') : (STYLE_CLASSES[val] || ''));
    }
  }
  pushText(raw.slice(last));
  return segments;
}

function applyRates(segments, level){
  const result = [];
  for(const segment of segments){
    const rateMatch = segment.text && segment.text.match(/^~~([^~]*)~~$/);
    if(!rateMatch){
      result.push(segment);
      continue;
    }
    const previous = result[result.length - 1];
    const rate = parseFloat(rateMatch[1]);
    if(isNaN(rate)){
      // HDP writes 0~~NaN~~ for values it couldn't resolve; show the text without the number
      if(previous && previous.number){
        result.pop();
      }
      continue;
    }
    if(previous && previous.number){
      const numberMatch = previous.text.match(/^(-?\d+(?:\.\d+)?)(.*)$/);
      if(numberMatch){
        result[result.length - 1] = { ...previous, text: roundLike(scaleValue(parseFloat(numberMatch[1]), rate, level), numberMatch[1]) + numberMatch[2], scaled: true };
      }
    }
    result.push({ ...segment, text: ` (${formatRate(rate)})` });
  }
  return result;
}

export function parseGameTooltip(raw, level = 1){
  if(!raw){
    return [];
  }
  const paragraphs = [[]];
  for(const segment of applyRates(tokenize(raw), level)){
    if(segment.break){
      // <n/><n/> is a paragraph break; a single <n/> is a line break
      const current = paragraphs[paragraphs.length - 1];
      if(current.length && current[current.length - 1].lineBreak){
        current.pop();
        paragraphs.push([]);
      }else{
        current.push({ lineBreak: true });
      }
      continue;
    }
    paragraphs[paragraphs.length - 1].push(segment);
  }
  return paragraphs
    .map(paragraph => paragraph.filter((segment, i) => !(segment.lineBreak && (i === 0 || i === paragraph.length - 1))))
    .filter(paragraph => paragraph.some(segment => segment.questIcon || (segment.text && segment.text.trim())));
}
