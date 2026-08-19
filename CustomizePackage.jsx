"use client";
// Updated CustomizePackage.jsx
// Now loads itemsData dynamically from PHP backend via fetchCustomOptions()
// Falls back to hardcoded data if API is unavailable

import React, { useState, useEffect } from 'react';
import {
  FaLaptopCode, FaShoppingCart, FaBriefcase, FaPaintBrush,
  FaSearch, FaPenNib, FaCreditCard, FaShareAlt,
  FaCheckCircle, FaTrash, FaGripVertical, FaPlus, FaMinus
} from 'react-icons/fa';
import { motion, AnimatePresence } from "framer-motion";
import { fetchCustomOptions, submitLead } from '@/lib/contentApi';

// ─── Icon map (add more here if you add icons in admin) ───────────────────────
const ICON_MAP = {
  FaShoppingCart: <FaShoppingCart />,
  FaBriefcase:    <FaBriefcase />,
  FaLaptopCode:   <FaLaptopCode />,
  FaPaintBrush:   <FaPaintBrush />,
  FaSearch:       <FaSearch />,
  FaPenNib:       <FaPenNib />,
  FaCreditCard:   <FaCreditCard />,
  FaShareAlt:     <FaShareAlt />,
};

// ─── Fallback static data (used if API is down) ───────────────────────────────
const FALLBACK_ITEMS = {
  websiteType: [
    { id: 'ecommerce', title: 'E-Commerce',   iconName: 'FaShoppingCart', price: 500 },
    { id: 'corporate', title: 'Corporate',    iconName: 'FaBriefcase',    price: 300 },
    { id: 'portfolio', title: 'Portfolio',    iconName: 'FaLaptopCode',   price: 200 },
    { id: 'landing',   title: 'Landing Page', iconName: 'FaPaintBrush',   price: 100 },
  ],
  pages: [
    { id: '1-5',   title: '1 - 5 Pages',   price: 0   },
    { id: '5-10',  title: '5 - 10 Pages',  price: 100 },
    { id: '10-20', title: '10 - 20 Pages', price: 250 },
    { id: '20+',   title: '20+ Pages',     price: 500 },
  ],
  design: [
    { id: 'template', title: 'Template Based', desc: 'Standard pre-made design',          price: 0   },
    { id: 'custom',   title: 'Custom Design',  desc: 'Tailored to your brand',            price: 300 },
    { id: 'premium',  title: 'Premium UI/UX',  desc: 'High-end interactions & animations', price: 600 },
  ],
  features: [
    { id: 'seo',     title: 'SEO Setup',                iconName: 'FaSearch',      price: 150 },
    { id: 'content', title: 'Content Writing',          iconName: 'FaPenNib',      price: 200 },
    { id: 'payment', title: 'Payment Gateway',          iconName: 'FaCreditCard',  price: 100 },
    { id: 'social',  title: 'Social Media Integrations',iconName: 'FaShareAlt',   price: 50  },
  ],
};

// Attach React icon elements to item objects
function hydrateIcons(items) {
  return Object.fromEntries(
    Object.entries(items).map(([cat, arr]) => [
      cat,
      arr.map(item => ({
        ...item,
        icon: item.iconName ? ICON_MAP[item.iconName] || null : null,
      })),
    ])
  );
}

export default function CustomizePackage() {
  const [itemsData, setItemsData] = useState(hydrateIcons(FALLBACK_ITEMS));
  const [loadingOptions, setLoadingOptions] = useState(true);

  const [selections, setSelections] = useState({
    websiteType: null, pages: null, design: null, features: []
  });

  const [isMobile, setIsMobile] = useState(false);
  const [isDraggingOver, setIsDraggingOver] = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [contactInfo, setContactInfo] = useState({ name: '', email: '', phone: '', message: '' });
  const [submitStatus, setSubmitStatus] = useState({ type: '', message: '' });
  const [submitting, setSubmitting] = useState(false);

  // ─── Load options from PHP API ─────────────────────────────────────────────
  useEffect(() => {
    fetchCustomOptions().then(data => {
      if (data) setItemsData(hydrateIcons(data));
    }).finally(() => setLoadingOptions(false));
  }, []);

  useEffect(() => {
    const check = () => setIsMobile(window.innerWidth < 1024);
    check();
    window.addEventListener('resize', check);
    return () => window.removeEventListener('resize', check);
  }, []);

  const cardVariants = {
    initial: { opacity: 0, y: 20 },
    animate: { opacity: 1, y: 0, transition: { duration: 0.4, ease: "easeOut" } },
  };
  const fadeIn = { initial: { opacity: 0, y: 20 }, animate: { opacity: 1, y: 0, transition: { duration: 0.4 } } };
  const itemAnim = { initial: { opacity: 0, scale: 0.95 }, animate: { opacity: 1, scale: 1 }, exit: { opacity: 0, scale: 0.9 } };
  const itemHover = { whileHover: { scale: 1.04, y: -4 } };

  const calculateTotal = () => {
    let total = 0;
    if (selections.websiteType) total += selections.websiteType.price;
    if (selections.pages)       total += selections.pages.price;
    if (selections.design)      total += selections.design.price;
    selections.features.forEach(f => total += f.price);
    return total;
  };

  const handleMobileTap = (item, category) => {
    if (category === 'features') {
      setSelections(prev => {
        const exists = prev.features.some(f => f.id === item.id);
        return { ...prev, features: exists ? prev.features.filter(f => f.id !== item.id) : [...prev.features, item] };
      });
    } else {
      setSelections(prev => ({ ...prev, [category]: prev[category]?.id === item.id ? null : item }));
    }
  };

  const isSelected = (item, category) => {
    if (category === 'features') return selections.features.some(f => f.id === item.id);
    return selections[category]?.id === item.id;
  };

  const handleDragStart = (e, item, category) => {
    e.dataTransfer.setData('application/json', JSON.stringify({ itemId: item.id, category }));
    e.dataTransfer.effectAllowed = 'copyMove';
    setTimeout(() => { e.target.style.opacity = '0.5'; }, 0);
  };
  const handleDragEnd   = (e) => { e.target.style.opacity = '1'; setIsDraggingOver(false); };
  const handleDragOver  = (e) => { e.preventDefault(); setIsDraggingOver(true); e.dataTransfer.dropEffect = 'copy'; };
  const handleDragLeave = () => setIsDraggingOver(false);
  const handleDrop = (e) => {
    e.preventDefault(); setIsDraggingOver(false);
    try {
      const { itemId, category } = JSON.parse(e.dataTransfer.getData('application/json'));
      const fullItem = itemsData[category]?.find(i => i.id === itemId);
      if (!fullItem) return;
      setSelections(prev => {
        if (category === 'features') {
          if (!prev.features.some(f => f.id === fullItem.id)) return { ...prev, features: [...prev.features, fullItem] };
          return prev;
        }
        return { ...prev, [category]: fullItem };
      });
    } catch (err) { console.error("Drop failed", err); }
  };

  const removeSelection = (category, itemId = null) => {
    setSelections(prev => {
      if (category === 'features') return { ...prev, features: prev.features.filter(f => f.id !== itemId) };
      return { ...prev, [category]: null };
    });
  };

  const handleSubmit = () => {
    if (!selections.websiteType || !selections.pages || !selections.design) {
      alert("Please select at least a Website Type, Pages count, and Design type.");
      return;
    }
    setShowForm(true);
  };

  const handleFinalSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setSubmitStatus({ type: '', message: '' });

    try {
      await submitLead({
        source: 'custom-package',
        ...contactInfo,
        package: {
          total:       calculateTotal(),
          websiteType: selections.websiteType,
          pages:       selections.pages,
          design:      selections.design,
          features:    selections.features,
        },
        message: contactInfo.message || `Custom package request. Estimated total: $${calculateTotal()}`,
      });

      setSubmitStatus({ type: 'success', message: 'Request submitted! We will contact you shortly.' });
      setContactInfo({ name: '', email: '', phone: '', message: '' });
      setSelections({ websiteType: null, pages: null, design: null, features: [] });
      setTimeout(() => setShowForm(false), 900);
    } catch (error) {
      setSubmitStatus({ type: 'error', message: error.message });
    } finally {
      setSubmitting(false);
    }
  };

  // ─── Reusable Item Card ────────────────────────────────────────────────────
  const ItemCard = ({ item, category, gripClass }) => {
    const selected = isSelected(item, category);
    return (
      <motion.div
        key={item.id}
        draggable={!isMobile}
        onDragStart={!isMobile ? (e) => handleDragStart(e, item, category) : undefined}
        onDragEnd={!isMobile ? handleDragEnd : undefined}
        onClick={isMobile ? () => handleMobileTap(item, category) : undefined}
        {...(!isMobile ? itemHover : {})}
        whileTap={isMobile ? { scale: 0.97 } : {}}
        className={`group relative bg-white/80 backdrop-blur-md p-4 rounded-xl border transition-all duration-300 overflow-hidden
          ${isMobile ? 'cursor-pointer select-none' : 'cursor-grab active:cursor-grabbing'}
          ${selected ? 'border-pink-400 shadow-lg ring-2 ring-pink-300' : 'border-slate-200 shadow-md hover:shadow-xl'}`}
      >
        <div className={`absolute inset-0 opacity-0 group-hover:opacity-100 transition duration-300 ${selected ? 'opacity-100' : ''} bg-gradient-to-r from-pink-500/10 via-orange-400/10 to-purple-500/10`} />
        {isMobile && selected && <span className="absolute top-2 right-2 text-pink-500 text-xs"><FaCheckCircle /></span>}
        <div className="relative flex items-center gap-3">
          {isMobile ? (
            <div className={`text-sm ${selected ? 'text-pink-500' : 'text-slate-400'} transition`}>
              {selected ? <FaMinus /> : <FaPlus />}
            </div>
          ) : (
            <div className={`${gripClass} transition`}><FaGripVertical /></div>
          )}
          {item.icon && (
            <div className={`text-xl ${selected ? 'text-pink-600 scale-110' : 'text-pink-600 group-hover:scale-110'} transition`}>
              {item.icon}
            </div>
          )}
          <div>
            <h3 className="font-medium text-sm text-slate-800">{item.title}</h3>
            {item.desc && <p className="text-xs text-slate-500 mt-0.5">{item.desc}</p>}
            {item.price > 0 && <p className="text-xs font-semibold text-pink-600 mt-0.5">+${item.price}</p>}
          </div>
        </div>
      </motion.div>
    );
  };

  return (
    <section className="py-36 text-slate-900 min-h-screen px-4 md:px-8 bg-slate-50">
      <div className="container mx-auto">
        <div className="text-center my-12">
          <motion.h2
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7, delay: 0.1, ease: [0.16, 1, 0.3, 1] }}
            className="text-5xl md:text-6xl lg:text-7xl font-black leading-[0.95] tracking-[-0.03em] text-slate-900"
          >
            Build Your
            <br />
            <span style={{ background: "linear-gradient(135deg, #E879F9 0%, #A855F7 40%, #38BDF8 100%)", WebkitBackgroundClip: "text", WebkitTextFillColor: "transparent", backgroundClip: "text" }}>
              Custom Package
            </span>
          </motion.h2>
          <p className="text-slate-500 text-lg md:text-xl mt-6 max-w-2xl mx-auto">
            {isMobile ? "Tap the options below to add them to your package." : "Drag and drop the features you need into your package box to get an instant estimate."}
          </p>
          {loadingOptions && <p className="text-slate-400 text-sm mt-3">Loading options...</p>}
        </div>

        <div className="flex flex-col lg:flex-row gap-8">
          <div className="lg:w-1/2 space-y-10">

            {/* Website Type */}
            <motion.div variants={cardVariants} initial="initial" animate="animate" className="relative p-[1px] rounded-2xl">
              <div className="bg-white/70 backdrop-blur-xl p-6 rounded-2xl border border-white/40 shadow-xl">
                <h2 className="text-xl font-semibold mb-5 flex items-center gap-3 text-slate-900 tracking-tight">
                  <span className="bg-gradient-to-br from-pink-600 to-orange-500 text-white w-7 h-7 rounded-full flex items-center justify-center text-sm shadow-lg">1</span>
                  Website Type
                </h2>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {itemsData.websiteType.map(item => <ItemCard key={item.id} item={item} category="websiteType" gripClass="text-slate-400 group-hover:text-pink-500" />)}
                </div>
              </div>
            </motion.div>

            {/* Pages */}
            <motion.div variants={cardVariants} initial="initial" animate="animate" className="relative p-[1px] rounded-2xl">
              <div className="bg-white/70 backdrop-blur-xl p-6 rounded-2xl border border-white/40 shadow-xl">
                <h2 className="text-xl font-semibold mb-5 flex items-center gap-3 text-slate-900 tracking-tight">
                  <span className="bg-gradient-to-br from-blue-600 to-indigo-500 text-white w-7 h-7 rounded-full flex items-center justify-center text-sm shadow-lg">2</span>
                  Number of Pages
                </h2>
                <div className="grid grid-cols-2 gap-4">
                  {itemsData.pages.map(item => <ItemCard key={item.id} item={item} category="pages" gripClass="text-slate-400 group-hover:text-blue-500" />)}
                </div>
              </div>
            </motion.div>

            {/* Design */}
            <motion.div variants={cardVariants} initial="initial" animate="animate" className="relative p-[1px] rounded-2xl">
              <div className="bg-white/70 backdrop-blur-xl p-6 rounded-2xl border border-white/40 shadow-xl">
                <h2 className="text-xl font-semibold mb-5 flex items-center gap-3 text-slate-900 tracking-tight">
                  <span className="bg-gradient-to-br from-purple-600 to-pink-500 text-white w-7 h-7 rounded-full flex items-center justify-center text-sm shadow-lg">3</span>
                  Design Quality
                </h2>
                <div className="space-y-4">
                  {itemsData.design.map(item => <ItemCard key={item.id} item={item} category="design" gripClass="text-slate-400 group-hover:text-purple-500" />)}
                </div>
              </div>
            </motion.div>

            {/* Features */}
            <motion.div variants={cardVariants} initial="initial" animate="animate" className="relative p-[1px] rounded-2xl">
              <div className="bg-white/70 backdrop-blur-xl p-6 rounded-2xl border border-white/40 shadow-xl">
                <h2 className="text-xl font-semibold mb-5 flex items-center gap-3 text-slate-900 tracking-tight">
                  <span className="bg-gradient-to-br from-green-600 to-teal-500 text-white w-7 h-7 rounded-full flex items-center justify-center text-sm shadow-lg">4</span>
                  Additional Features
                </h2>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {itemsData.features.map(item => <ItemCard key={item.id} item={item} category="features" gripClass="text-slate-400 group-hover:text-green-500" />)}
                </div>
              </div>
            </motion.div>
          </div>

          {/* Right: Package summary — unchanged from original */}
          <div className="lg:w-1/2">
            <div className="sticky top-24">
              {!showForm ? (
                <motion.div
                  variants={fadeIn} initial="initial" animate="animate"
                  onDrop={!isMobile ? handleDrop : undefined}
                  onDragOver={!isMobile ? handleDragOver : undefined}
                  onDragLeave={!isMobile ? handleDragLeave : undefined}
                  className={`relative p-[1px] rounded-3xl transition-all duration-300 ${isDraggingOver ? "bg-gradient-to-r from-[#E879F9] to-[#38BDF8] scale-[1.02]" : "bg-gradient-to-br from-slate-200 to-slate-300"}`}
                >
                  <div className="bg-white/70 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/40 min-h-[600px] flex flex-col">
                    <div className="flex justify-between items-end mb-8 border-b border-slate-100 pb-6">
                      <div>
                        <h2 className="text-2xl font-semibold text-slate-900 tracking-tight">Your Package</h2>
                        <p className="text-sm text-slate-500 mt-1">{isMobile ? "Tap items on the left to build" : "Drag & build your custom setup"}</p>
                      </div>
                      <div className="text-right">
                        <span className="block text-slate-600 text-xs uppercase tracking-wider mb-1">Estimated Total</span>
                        <motion.span key={calculateTotal()} initial={{ scale: 0.9 }} animate={{ scale: 1 }}
                          className="text-4xl font-extrabold bg-gradient-to-r from-pink-600 to-orange-500 bg-clip-text text-transparent">
                          ${calculateTotal()}
                        </motion.span>
                      </div>
                    </div>

                    <div className="flex-1 space-y-5">
                      {[
                        { key: "websiteType", label: "Website Type",  render: (val) => <div className="flex items-center gap-3"><span className="text-pink-600">{val.icon}</span><span>{val.title}</span></div> },
                        { key: "pages",       label: "Pages",         render: (val) => <span>{val.title}</span> },
                        { key: "design",      label: "Design Level",  render: (val) => <span>{val.title}</span> },
                      ].map((block) => (
                        <div key={block.key} className="bg-white/60 backdrop-blur-md rounded-xl p-4 border border-slate-200">
                          <div className="text-[10px] font-bold tracking-widest text-slate-400 mb-2 uppercase">{block.label}</div>
                          <AnimatePresence mode="wait">
                            {selections[block.key] ? (
                              <motion.div key="filled" variants={itemAnim} initial="initial" animate="animate" exit="exit"
                                className="flex justify-between items-center bg-white p-3 rounded-lg border border-pink-200 shadow-sm hover:shadow-md transition">
                                <div className="font-medium text-sm text-slate-800">{block.render(selections[block.key])}</div>
                                <button onClick={() => removeSelection(block.key)} className="text-slate-400 hover:text-red-500 transition"><FaTrash /></button>
                              </motion.div>
                            ) : (
                              <motion.div key="empty" variants={itemAnim} initial="initial" animate="animate" exit="exit"
                                className="text-slate-400 text-sm italic py-3 flex items-center justify-center border-2 border-dashed border-slate-300 rounded-lg bg-white">
                                {isMobile ? `Tap to select ${block.label}` : `Drag ${block.label} here`}
                              </motion.div>
                            )}
                          </AnimatePresence>
                        </div>
                      ))}

                      <div className="bg-white/60 backdrop-blur-md rounded-xl p-4 border border-slate-200 flex-1">
                        <div className="text-[10px] font-bold tracking-widest text-slate-400 mb-2 uppercase">Extra Features</div>
                        <AnimatePresence>
                          {selections.features.length > 0 ? (
                            <motion.div className="space-y-2">
                              {selections.features.map(f => (
                                <motion.div key={f.id} variants={itemAnim} initial="initial" animate="animate" exit="exit"
                                  className="flex justify-between items-center bg-white p-3 rounded-lg border border-pink-200 shadow-sm hover:shadow-md transition">
                                  <div className="flex items-center gap-3">
                                    <span className="text-pink-600">{f.icon}</span>
                                    <span className="text-sm font-medium text-slate-800">{f.title}</span>
                                  </div>
                                  <button onClick={() => removeSelection("features", f.id)} className="text-slate-400 hover:text-red-500 transition"><FaTrash /></button>
                                </motion.div>
                              ))}
                            </motion.div>
                          ) : (
                            <motion.div variants={itemAnim} initial="initial" animate="animate"
                              className="text-slate-400 text-sm italic py-6 flex flex-col items-center justify-center border-2 border-dashed border-slate-300 rounded-lg bg-white">
                              <span>{isMobile ? "Tap Features to add" : "Drag Features here"}</span>
                              <span className="text-xs mt-1">(Optional)</span>
                            </motion.div>
                          )}
                        </AnimatePresence>
                      </div>
                    </div>

                    <motion.button whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.97 }} onClick={handleSubmit}
                      className="mt-8 group relative flex items-center justify-center gap-3 w-full px-10 py-5 bg-gradient-to-r from-[#FF1F8E] to-[#FF0055] rounded-full text-white font-bold tracking-[0.2em] uppercase overflow-hidden shadow-[0_15px_35px_rgba(255,31,142,0.3)] hover:shadow-[0_20px_40px_rgba(255,31,142,0.4)] hover:-translate-y-1 transition-all duration-300">
                      Complete My Package <FaCheckCircle className="inline ml-2 relative z-10 group-hover:translate-x-1 transition-transform" />
                      <div className="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity z-0" />
                    </motion.button>
                  </div>
                </motion.div>
              ) : (
                <motion.div variants={fadeIn} initial="initial" animate="animate"
                  className="relative p-[1px] rounded-3xl bg-gradient-to-br from-[#E879F9] to-[#38BDF8]">
                  <div className="bg-white/80 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/40 min-h-[600px] flex flex-col">
                    <div className="mb-8 border-b border-slate-100 pb-6 flex justify-between">
                      <div><h2 className="text-2xl font-semibold text-slate-900">Finalize Request</h2><p className="text-sm text-slate-500 mt-1">Enter your details</p></div>
                      <button onClick={() => setShowForm(false)} className="text-sm text-pink-600 hover:text-pink-500">← Edit</button>
                    </div>
                    <form onSubmit={handleFinalSubmit} className="space-y-5 flex-1">
                      {["name", "email", "phone"].map(field => (
                        <div key={field}>
                          <label className="block text-sm font-medium text-slate-600 mb-1 capitalize">{field}</label>
                          <input type={field === "email" ? "email" : "text"} required={field !== "phone"}
                            value={contactInfo[field]} onChange={e => setContactInfo({ ...contactInfo, [field]: e.target.value })}
                            className="w-full bg-white/70 border border-slate-200 rounded-xl px-4 py-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition" />
                        </div>
                      ))}
                      <textarea rows="4" placeholder="Additional notes..." value={contactInfo.message}
                        onChange={e => setContactInfo({ ...contactInfo, message: e.target.value })}
                        className="w-full bg-white/70 border border-slate-200 rounded-xl px-4 py-3 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition" />
                      <motion.button whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.97 }} type="submit" disabled={submitting}
                        className="mt-4 group relative flex items-center justify-center gap-3 w-full px-10 py-5 bg-gradient-to-r from-[#FF1F8E] to-[#FF0055] rounded-full text-white font-bold tracking-[0.2em] uppercase overflow-hidden shadow-[0_15px_35px_rgba(255,31,142,0.3)] hover:shadow-[0_20px_40px_rgba(255,31,142,0.4)] hover:-translate-y-1 transition-all duration-300">
                        {submitting ? 'Submitting...' : 'Submit Request'} <FaCheckCircle className="inline ml-2 relative z-10 group-hover:translate-x-1 transition-transform" />
                        <div className="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity z-0" />
                      </motion.button>
                      {submitStatus.message && (
                        <p className={`text-sm font-semibold ${submitStatus.type === 'success' ? 'text-emerald-600' : 'text-red-500'}`}>
                          {submitStatus.message}
                        </p>
                      )}
                    </form>
                  </div>
                </motion.div>
              )}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
