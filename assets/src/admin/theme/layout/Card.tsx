import React from 'react';
import cn from '../../utils/classnames';
import { isCardClosed, setCardClosed } from '../../utils/localstorage';
import contentStyles from '../Content.css';
import styles from './Card.css';

const Card: React.FC<{
  className?: string;
  canToggleKey?: string;
  title?: string;
  children?: any;
  toggleButtonClose?: Function;
  rightContent?: JSX.Element;
  stickyHeader?: boolean;
}> = ({
  className = '',
  canToggleKey = '',
  title = '',
  children,
  toggleButtonClose,
  rightContent = null,
  stickyHeader = false,
}) => {
  const [open, setOpen] = React.useState<boolean>(!isCardClosed(canToggleKey));

  const canToggle = React.useMemo(() => canToggleKey !== '', [canToggleKey]);

  React.useEffect(() => {
    canToggle && setCardClosed(canToggleKey, !open);
  }, [open]);

  return (
    <div
      className={cn(className, styles.card, {
        [styles.cardClosed]: !open && canToggle,
      })}
    >
      {title && (
        <div
          className={cn(styles.heading, {
            [styles.headingSticky]: stickyHeader,
          })}
        >
          <h2 className={styles.title}>{title}</h2>
          {(canToggle || Boolean(toggleButtonClose)) && (
            <button
              className={cn(styles.toggleButton)}
              onClick={() =>
                Boolean(toggleButtonClose)
                  ? toggleButtonClose()
                  : setOpen(!open)
              }
            >
              <span className="screen-reader-text">Toggle panel</span>
              <span
                className={cn('dashicons', styles.toggleButtonIcon, {
                  'dashicons-no-alt': Boolean(toggleButtonClose),
                  'dashicons-arrow-up-alt2':
                    open && !Boolean(toggleButtonClose),
                  'dashicons-arrow-down-alt2':
                    !open && !Boolean(toggleButtonClose),
                })}
              />
            </button>
          )}
          {rightContent && (
            <div className={styles.headingRightContent}>{rightContent}</div>
          )}
        </div>
      )}
      <div className={cn(styles.content, contentStyles.content)}>
        {children}
      </div>
    </div>
  );
};

export default Card;
