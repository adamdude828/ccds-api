'use client';

import React, { useState, useEffect } from 'react';
import { Button, Select, Input } from '@challenger-school/do-git-mis-components-storybook';
import Modal from '@/components/common/Modal';
import { VideoService } from '@/services/videoService';
import { Grade, Subject, VideoMode } from '@/types/video';

interface MassActionModalProps {
  isOpen: boolean;
  onClose: () => void;
  selectedVideoIds: number[];
  onComplete: () => void;
}

type ActionType = 'update_meta' | '';

export default function MassActionModal({
  isOpen,
  onClose,
  selectedVideoIds,
  onComplete,
}: MassActionModalProps) {
  const [step, setStep] = useState<'select' | 'configure'>('select');
  const [action, setAction] = useState<ActionType>('');
  const [subjectId, setSubjectId] = useState('');
  const [gradeId, setGradeId] = useState('');
  const [videoModeId, setVideoModeId] = useState('');
  const [day, setDay] = useState('');
  const [grades, setGrades] = useState<Grade[]>([]);
  const [subjects, setSubjects] = useState<Subject[]>([]);
  const [videoModes, setVideoModes] = useState<VideoMode[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (isOpen) {
      loadFormData();
    }
  }, [isOpen]);

  const loadFormData = async () => {
    try {
      const [gradesData, subjectsData, modesData] = await Promise.all([
        VideoService.getGrades(),
        VideoService.getSubjects(),
        VideoService.getVideoModes(),
      ]);
      setGrades(gradesData);
      setSubjects(subjectsData);
      setVideoModes(modesData);
    } catch (error) {
      console.error('Failed to load form data:', error);
      setError('Failed to load form data');
    }
  };

  const handleClose = () => {
    setStep('select');
    setAction('');
    setSubjectId('');
    setGradeId('');
    setVideoModeId('');
    setDay('');
    setError(null);
    onClose();
  };

  const handleNext = () => {
    if (action === 'update_meta') {
      setStep('configure');
    }
  };

  const handleExecute = async () => {
    setLoading(true);
    setError(null);

    try {
      const updateData: any = {
        video_ids: selectedVideoIds,
      };

      if (subjectId) updateData.subject_id = parseInt(subjectId);
      if (gradeId) updateData.grade_id = parseInt(gradeId);
      if (videoModeId) updateData.video_mode_id = parseInt(videoModeId);
      if (day) updateData.day = parseInt(day);

      await VideoService.massUpdate(updateData);
      onComplete();
      handleClose();
    } catch (error) {
      console.error('Failed to execute mass action:', error);
      setError('Failed to execute mass action. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const actionOptions = [
    { value: 'update_meta', label: 'Update Meta' },
  ];

  const gradeOptions = [
    { value: '', label: 'No change' },
    ...grades.map(grade => ({
      value: grade.id.toString(),
      label: grade.display_name,
    })),
  ];

  const subjectOptions = [
    { value: '', label: 'No change' },
    ...subjects.map(subject => ({
      value: subject.id.toString(),
      label: subject.display_name,
    })),
  ];

  const modeOptions = [
    { value: '', label: 'No change' },
    ...videoModes.map(mode => ({
      value: mode.id.toString(),
      label: mode.display_name,
    })),
  ];

  return (
    <Modal
      isOpen={isOpen}
      onClose={handleClose}
      title="Mass Action"
      size="md"
    >
      {error && (
        <div className="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
          {error}
        </div>
      )}

      {step === 'select' && (
        <div className="space-y-4">
          <p className="text-gray-600">
            Select an action to apply to {selectedVideoIds.length} selected video(s).
          </p>
          
          <Select
            options={actionOptions}
            value={actionOptions.find(opt => opt.value === action)}
            onChange={(option) => setAction(option.value as ActionType)}
          />

          <div className="flex justify-end space-x-3">
            <Button
              label="Cancel"
              variant="secondary"
              onClick={handleClose}
            />
            <Button
              label="Next"
              variant="primary"
              onClick={handleNext}
              disabled={!action}
            />
          </div>
        </div>
      )}

      {step === 'configure' && action === 'update_meta' && (
        <div className="space-y-4">
          <p className="text-gray-600">
            Update metadata for {selectedVideoIds.length} selected video(s).
            Leave fields empty to keep existing values.
          </p>

          <div>
            <label className="block text-sm font-medium mb-2">Subject</label>
            <Select
              options={subjectOptions}
              value={subjectOptions.find(opt => opt.value === subjectId)}
              onChange={(option) => setSubjectId(option.value)}
            />
          </div>

          <div>
            <label className="block text-sm font-medium mb-2">Grade</label>
            <Select
              options={gradeOptions}
              value={gradeOptions.find(opt => opt.value === gradeId)}
              onChange={(option) => setGradeId(option.value)}
            />
          </div>

          <div>
            <label className="block text-sm font-medium mb-2">Video Mode</label>
            <Select
              options={modeOptions}
              value={modeOptions.find(opt => opt.value === videoModeId)}
              onChange={(option) => setVideoModeId(option.value)}
            />
          </div>

          <div>
            <label className="block text-sm font-medium mb-2">Day</label>
            <Input
              value={day}
              onChange={(e) => setDay(e.target.value)}
              type="number"
              min="1"
              placeholder="No change"
            />
          </div>

          <div className="flex justify-end space-x-3">
            <Button
              label="Back"
              variant="secondary"
              onClick={() => setStep('select')}
              disabled={loading}
            />
            <Button
              label="Execute"
              variant="primary"
              onClick={handleExecute}
              disabled={loading || (!subjectId && !gradeId && !videoModeId && !day)}
            />
          </div>
        </div>
      )}
    </Modal>
  );
}